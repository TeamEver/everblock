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
        } catch (Exception $exception) {
            $error = $exception->getMessage();
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
     * used from here. We therefore read the native `psAdmin` cookie, exactly like
     * Tools::isAllowedToBypassMaintenance() does, and replay the checks of isLoggedBack().
     */
    protected function assertBackOfficeAccess(): void
    {
        $employee = $this->resolveBackOfficeEmployee();

        if (!$employee instanceof Employee) {
            throw $this->accessDenied($this->translate('The block preview is restricted to logged-in back office employees.'));
        }

        // Expose the employee so the native token helpers compute the same token as the back office.
        $this->context->employee = $employee;

        if (!$this->isEmployeeGranted($employee)) {
            throw $this->accessDenied($this->translate('You do not have permission to preview Ever Block blocks.'));
        }

        $this->assertValidToken();
        $this->assertShopAccess($employee);
    }

    /**
     * @return Employee|null The employee owning a valid back office session, null otherwise
     */
    protected function resolveBackOfficeEmployee()
    {
        return EverblockBackOfficeGuard::getLoggedEmployee();
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

    protected function assertValidToken(): void
    {
        $token = (string) Tools::getValue(EverblockPreviewToken::PARAM_TOKEN);
        if ($token === '') {
            throw $this->accessDenied($this->translate('Missing preview token.'));
        }

        // Signature issued by EverblockAdminController::buildPreviewUrl(). Both sides compute
        // the same HMAC over an explicit payload, unlike Tools::getAdminTokenLite() whose value
        // depends on Context::getContext()->employee->id and on the tab id.
        $expires = (int) Tools::getValue(EverblockPreviewToken::PARAM_EXPIRES);
        $nonce = (string) Tools::getValue(EverblockPreviewToken::PARAM_NONCE);
        $blockId = (int) Tools::getValue(EverblockPreviewToken::PARAM_BLOCK);
        $shopId = (int) Tools::getValue(EverblockPreviewToken::PARAM_SHOP, (int) $this->context->shop->id);
        $languageId = (int) Tools::getValue(EverblockPreviewToken::PARAM_LANG, (int) $this->context->language->id);

        if ($expires > 0
            && EverblockPreviewToken::isFresh($expires)
            && EverblockPreviewToken::verify($blockId, $shopId, $languageId, $expires, $nonce, $token)
        ) {
            return;
        }

        // Legacy fallback: preview links generated before the signed token was introduced, and
        // still open in a back office tab. Kept because it can only widen acceptance for an
        // already authenticated employee, never narrow it.
        $legacyTokens = [
            Tools::getAdminTokenLite('AdminEverBlock'),
            Tools::getAdminTokenLite('AdminEverBlockConfiguration'),
            Tools::getAdminTokenLite('AdminEverBlockHook'),
            Tools::getAdminTokenLite('AdminModules'),
        ];

        foreach ($legacyTokens as $legacyToken) {
            if (is_string($legacyToken) && $legacyToken !== '' && hash_equals($legacyToken, $token)) {
                return;
            }
        }

        $this->logTokenMismatch($token, $expires, $nonce);

        throw $this->accessDenied($this->translate('Invalid preview token.'));
    }

    /**
     * Logs what differed, without ever writing a token value to the logs.
     */
    protected function logTokenMismatch(string $token, int $expires, string $nonce): void
    {
        if (!class_exists('PrestaShopLogger')) {
            return;
        }

        $employeeId = isset($this->context->employee) && $this->context->employee
            ? (int) $this->context->employee->id
            : 0;

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
