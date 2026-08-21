<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Everblock\Tools\Service\EverblockBackOfficeGuard;
use Everblock\Tools\Service\EverblockPreviewBuilder;
use Everblock\Tools\Service\EverblockPreviewToken;

class EverblockPreviewModuleFrontController extends ModuleFrontController
{
    /**
     * Back office tab used as the ACL reference for the block preview.
     */
    const PREVIEW_TAB = 'AdminEverBlock';

    /** @var EverBlockClass|null */
    protected $block;

    /** @var int|null Memoized result of verifyProofEmployeeId() */
    protected $proofEmployeeId;

    public function initContent()
    {
        parent::initContent();

        $error = null;
        $previewData = [
            'html' => '',
            'info' => [],
            'hook' => '',
        ];

        try {
            $this->assertBackOfficeAccess();
            $this->block = $this->loadBlock();
            $previewParameters = $this->collectPreviewParameters();
            if (!$this->module instanceof Everblock) {
                throw new Exception('Invalid module instance.');
            }
            $builder = new EverblockPreviewBuilder($this->module, $this->context);
            $previewData = $builder->buildPreview($this->block, $previewParameters);
        } catch (Throwable $exception) {
            // Throwable, not Exception: rendering a block executes arbitrary module code and
            // Smarty templates, and a PHP Error there used to escape this catch and kill the
            // whole request instead of showing the message on the preview page.
            $error = $exception->getMessage();

            if (class_exists('PrestaShopLogger')) {
                PrestaShopLogger::addLog(
                    sprintf(
                        'Everblock preview: rendering failed for block #%d (%s in %s line %d)',
                        (int) Tools::getValue(EverblockPreviewToken::PARAM_BLOCK),
                        get_class($exception),
                        basename($exception->getFile()),
                        (int) $exception->getLine()
                    ),
                    3
                );
            }
        }

        $this->context->smarty->assign([
            'everblock_preview_error' => $error,
            'everblock_preview_html' => $previewData['html'],
            'everblock_preview_info' => $previewData['info'],
            'everblock_preview_hook' => $previewData['hook'],
            'everblock_preview_block' => $this->block,
            'everblock_preview_return_url' => $this->getReturnUrl(),
        ]);

        $this->setTemplate('module:everblock/views/templates/front/preview.tpl');
    }

    /**
     * The preview renders blocks in an arbitrary shop/language/customer context, so it must
     * stay strictly reserved to an authenticated back office employee.
     *
     * $context->employee is never populated on the front office (config/config.inc.php only
     * builds it when _PS_ADMIN_DIR_ is defined), and Employee::isLoggedBack() relies either on
     * the front office cookie (PS 8) or on the admin Symfony firewall (PS 9): neither can be
     * used from here.
     *
     * The gate is therefore the signed proof minted by
     * EverblockAdminController::previewRedirectAction(), a back office route that runs inside the
     * PrestaShop admin firewall (authenticated employee + Ever Block ACL + native CSRF token).
     * The proof names the employee it was issued for, so the ACL and the multistore check below
     * are evaluated against a real Employee object.
     *
     * The `psAdmin` cookie is deliberately NOT the gate: PrestaShop 9 only writes it on login
     * success (EmployeeSessionSubscriber::updateLegacyCookie() is called with $write = false on
     * every other request), so its content cannot be relied on from the front office. It is still
     * used as a reinforcement when it happens to be readable.
     */
    protected function assertBackOfficeAccess(): void
    {
        $employee = $this->assertValidToken();

        // Expose the employee so downstream preview code sees the same actor as the back office.
        $this->context->employee = $employee;

        if (!$this->isEmployeeGranted($employee)) {
            throw $this->accessDenied($this->translate('You do not have permission to preview Ever Block blocks.'));
        }

        $this->assertShopAccess($employee);
        $this->assertCookieAgreesWithProof($employee);
    }

    /**
     * Reinforcement, best effort: when the back office cookie does reach the front office, it
     * must designate the very employee the proof was issued for. When it cannot be read, the
     * signed proof stands on its own.
     */
    protected function assertCookieAgreesWithProof(Employee $employee): void
    {
        $cookieEmployee = EverblockBackOfficeGuard::getLoggedEmployee();

        if ($cookieEmployee instanceof Employee && (int) $cookieEmployee->id !== (int) $employee->id) {
            if (class_exists('PrestaShopLogger')) {
                PrestaShopLogger::addLog(
                    sprintf(
                        'Everblock preview: proof issued for employee #%d but the back office cookie designates #%d',
                        (int) $employee->id,
                        (int) $cookieEmployee->id
                    ),
                    3
                );
            }

            throw $this->accessDenied($this->translate('Invalid preview token.'));
        }
    }

    /**
     * Native PrestaShop ACL check on the Ever Block tab (read permission).
     */
    protected function isEmployeeGranted(Employee $employee): bool
    {
        return EverblockBackOfficeGuard::isGrantedOnTab($employee, self::PREVIEW_TAB, 'READ');
    }

    protected function assertShopAccess(Employee $employee): void
    {
        $shopId = (int) Tools::getValue('id_shop', (int) $this->context->shop->id);

        if ($shopId > 0
            && method_exists($employee, 'hasAuthOnShop')
            && !$employee->hasAuthOnShop($shopId)
        ) {
            throw $this->accessDenied($this->translate('You do not have access to the requested shop.'));
        }
    }

    /**
     * A block preview is a back office tool: it has to keep working while the shop is closed.
     *
     * PrestaShop's own rule is the same — Tools::isAllowedToBypassMaintenance() lets an employee
     * through — but it relies on reading the `psAdmin` cookie, which PrestaShop 9 only writes on
     * login success. On a shop in maintenance the preview would therefore answer 503 (an empty
     * body that the web server replaces with its own "Service Unavailable" page).
     *
     * The signed proof is a stronger credential than the cookie check it replaces here: it is
     * valid for 120 seconds and can only be minted by an authenticated employee holding the Ever
     * Block permission. The full gate still runs in initContent().
     */
    protected function displayMaintenancePage()
    {
        if ($this->verifyProofEmployeeId() > 0) {
            return;
        }

        parent::displayMaintenancePage();
    }

    /**
     * Verifies the signed proof and returns the employee it was issued for.
     *
     * @return Employee
     */
    protected function assertValidToken(): Employee
    {
        $token = (string) Tools::getValue(EverblockPreviewToken::PARAM_TOKEN);
        if ($token === '') {
            throw $this->accessDenied($this->translate('Missing preview token.'));
        }

        $expires = (int) Tools::getValue(EverblockPreviewToken::PARAM_EXPIRES);
        $nonce = (string) Tools::getValue(EverblockPreviewToken::PARAM_NONCE);
        $employeeId = $this->verifyProofEmployeeId();

        if ($employeeId <= 0) {
            $this->logTokenMismatch($token, $expires, $nonce, (int) Tools::getValue(EverblockPreviewToken::PARAM_EMPLOYEE));

            throw $this->accessDenied($this->translate('Invalid preview token.'));
        }

        $employee = new Employee($employeeId);

        if (!Validate::isLoadedObject($employee) || !$employee->active) {
            $this->logTokenMismatch($token, $expires, $nonce, $employeeId);

            throw $this->accessDenied($this->translate('The block preview is restricted to logged-in back office employees.'));
        }

        return $employee;
    }

    /**
     * Signature check only, no side effect and no exception: returns the id of the employee the
     * proof was issued for, or 0. Memoized because it is called both from init() (maintenance) and
     * from initContent() (the real gate).
     *
     * Signature issued by EverblockAdminController::previewRedirectAction(). Both sides compute
     * the same HMAC over an explicit payload, unlike Tools::getAdminTokenLite() whose value
     * depends on Context::getContext()->employee->id and on the tab id, neither of which exists
     * on the front office.
     */
    protected function verifyProofEmployeeId(): int
    {
        if ($this->proofEmployeeId !== null) {
            return $this->proofEmployeeId;
        }

        $this->proofEmployeeId = 0;

        try {
            $token = (string) Tools::getValue(EverblockPreviewToken::PARAM_TOKEN);
            $expires = (int) Tools::getValue(EverblockPreviewToken::PARAM_EXPIRES);
            $nonce = (string) Tools::getValue(EverblockPreviewToken::PARAM_NONCE);
            $blockId = (int) Tools::getValue(EverblockPreviewToken::PARAM_BLOCK);
            $employeeId = (int) Tools::getValue(EverblockPreviewToken::PARAM_EMPLOYEE);
            $shopId = (int) Tools::getValue(EverblockPreviewToken::PARAM_SHOP, (int) $this->context->shop->id);
            $languageId = (int) Tools::getValue(EverblockPreviewToken::PARAM_LANG, (int) $this->context->language->id);

            if ($token !== ''
                && $employeeId > 0
                && $expires > 0
                && EverblockPreviewToken::isFresh($expires)
                && EverblockPreviewToken::verify($blockId, $shopId, $languageId, $employeeId, $expires, $nonce, $token)
            ) {
                $this->proofEmployeeId = $employeeId;
            }
        } catch (Throwable $exception) {
            $this->proofEmployeeId = 0;
        }

        return $this->proofEmployeeId;
    }

    /**
     * Logs what differed, without ever writing a token value to the logs.
     */
    protected function logTokenMismatch(string $token, int $expires, string $nonce, int $employeeId = 0): void
    {
        if (!class_exists('PrestaShopLogger')) {
            return;
        }

        PrestaShopLogger::addLog(
            sprintf(
                'Everblock preview: token mismatch (token length %d, signed params %s, expires %s, employee #%d, tab id %d)',
                strlen($token),
                ($expires > 0 && $nonce !== '') ? 'present' : 'absent',
                $expires > 0 ? ($this->isExpiredLabel($expires)) : 'none',
                $employeeId,
                (int) Tab::getIdFromClassName(self::PREVIEW_TAB)
            ),
            2
        );
    }

    private function isExpiredLabel(int $expires): string
    {
        if ($expires <= time()) {
            return 'expired';
        }

        return EverblockPreviewToken::isFresh($expires) ? 'fresh' : 'out of range';
    }

    /**
     * Flags the response as 403 and builds the exception the caller has to throw.
     */
    protected function accessDenied(string $message): Exception
    {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
            header('Status: 403 Forbidden');
        }

        return new Exception($message);
    }

    protected function loadBlock(): EverBlockClass
    {
        $blockId = (int) Tools::getValue('id_everblock');
        $languageId = (int) Tools::getValue('id_lang', (int) $this->context->language->id);
        $shopId = (int) Tools::getValue('id_shop', (int) $this->context->shop->id);

        $block = new EverBlockClass($blockId, $languageId, $shopId);

        if (!Validate::isLoadedObject($block)) {
            throw new Exception($this->translate('Unable to find the requested block.'));
        }

        return $block;
    }

    protected function collectPreviewParameters(): array
    {
        $keys = [
            'controller',
            'page_name',
            'id_product',
            'id_category',
            'id_customer',
            'id_lang',
            'id_shop',
            'id_currency',
            'id_cms',
            'id_cms_category',
            'id_manufacturer',
            'id_supplier',
            'id_cart',
            'id_order',
            'id_order_return',
            'position',
        ];

        $params = [];

        foreach ($keys as $key) {
            $value = Tools::getValue($key);

            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'controller' || $key === 'page_name') {
                $params[$key] = (string) $value;
                continue;
            }

            $params[$key] = (int) $value;
        }

        if (!isset($params['controller']) || $params['controller'] === '') {
            $params['controller'] = 'index';
        }

        if (!isset($params['id_lang'])) {
            $params['id_lang'] = (int) $this->context->language->id;
        }

        if (!isset($params['id_shop'])) {
            $params['id_shop'] = (int) $this->context->shop->id;
        }

        if (!isset($params['id_currency']) && isset($this->context->currency->id)) {
            $params['id_currency'] = (int) $this->context->currency->id;
        }

        return $params;
    }

    protected function getReturnUrl(): string
    {
        return $this->context->link->getAdminLink('AdminEverBlock');
    }

    protected function translate(string $message, array $parameters = []): string
    {
        return $this->context->getTranslator()->trans($message, $parameters, 'Modules.Everblock.Front');
    }
}
