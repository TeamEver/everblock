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

namespace Everblock\PrestaShopBundle\Controller\Admin;

use Everblock\PrestaShopBundle\Grid\Search\Filters\EverBlockFilters;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use Module;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShop\PrestaShop\Core\Toolbar\Button\ButtonCollection;
use PrestaShop\PrestaShop\Core\Toolbar\Button\LinkToolbarButton;
use PrestaShop\PrestaShop\Core\Toolbar\Button\SimpleButton;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tools;

class EverBlockController extends FrameworkBundleAdminController
{
    private const MODULE_NAME = 'everblock';
    private const TRANSLATION_DOMAIN = 'Modules.Everblock.Admin';

    public function indexAction(Request $request): Response
    {
        /** @var GridFactoryInterface $gridFactory */
        $gridFactory = $this->get('everblock.grid.factory.ever_block');

        $filters = new EverBlockFilters($request->query->get(EverBlockFilters::FILTER_ID, []));
        $grid = $gridFactory->getGrid($filters);

        $module = Module::getInstanceByName(self::MODULE_NAME);
        $legacyContext = $this->get('prestashop.adapter.legacy.context')->getContext();

        $presentedGrid = $this->presentGrid($grid);
        $toolbarButtons = $this->presentToolbarButtons($this->buildToolbarButtons($module));

        $templateVariables = [
            'layoutTitle' => $this->trans('HTML blocks Configuration', self::TRANSLATION_DOMAIN),
            'layoutHeaderToolbarButtons' => $toolbarButtons,
            'grid' => $presentedGrid,
            'notifications' => $this->collectNotifications(),
            'module' => $this->buildModuleViewModel($module),
            'stats' => $module->getModuleStatistics(),
            'displayUpgrade' => $module->checkLatestEverModuleVersion(),
            'shortcodeDocumentation' => ShortcodeDocumentationProvider::getDocumentation($module),
            'modulePublicPath' => _MODULE_DIR_ . self::MODULE_NAME . '/',
            'configurationLink' => $this->getConfigurationLink($module),
        ];

        return $this->render('@Modules/everblock/views/templates/admin/everblock/index.html.twig', $templateVariables);
    }

    private function buildToolbarButtons(Module $module): ButtonCollection
    {
        $collection = new ButtonCollection();
        $context = $this->get('prestashop.adapter.legacy.context')->getContext();
        $adminLink = $context->link->getAdminLink('AdminEverBlock', true, [], [
            'configure' => $module->name,
            'module_name' => $module->name,
        ]);

        $collection->add((new SimpleButton('add-everblock'))
            ->setLabel($this->trans('New block', self::TRANSLATION_DOMAIN))
            ->setIcon('add_circle_outline')
            ->setHref($context->link->getAdminLink('AdminEverBlock', true, [], [
                'addeverblock' => 1,
            ]))
        );

        $collection->add((new SimpleButton('clear-cache-everblock'))
            ->setLabel($this->trans('Clear cache', self::TRANSLATION_DOMAIN))
            ->setIcon('refresh')
            ->setHref($context->link->getAdminLink('AdminEverBlock', true, [], [
                'clearcache' => 1,
            ]))
        );

        $collection->add((new LinkToolbarButton('configure-everblock'))
            ->setLabel($this->trans('Configuration', self::TRANSLATION_DOMAIN))
            ->setIcon('settings')
            ->setHref($adminLink)
        );

        $collection->add((new LinkToolbarButton('download-example-everblock'))
            ->setLabel($this->trans('Download Excel tabs sample file', self::TRANSLATION_DOMAIN))
            ->setIcon('cloud_download')
            ->setHref($this->getExampleTabsUrl($module))
        );

        return $collection;
    }

    private function collectNotifications(): array
    {
        $context = $this->get('prestashop.adapter.legacy.context')->getContext();
        $controller = $context->controller;

        $notifications = [
            'success' => [],
            'error' => [],
            'warning' => [],
            'info' => [],
        ];

        if (null !== $controller) {
            if (property_exists($controller, 'confirmations')) {
                $notifications['success'] = (array) $controller->confirmations;
            }

            if (property_exists($controller, 'errors')) {
                $notifications['error'] = (array) $controller->errors;
            }

            if (property_exists($controller, 'warnings')) {
                $notifications['warning'] = (array) $controller->warnings;
            }

            if (property_exists($controller, 'informations')) {
                $notifications['info'] = (array) $controller->informations;
            }
        }

        $session = $this->get('session');
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            $flashKey = sprintf('everblock_%s', $type);
            if ($session->getFlashBag()->has($flashKey)) {
                $notifications[$type] = array_merge(
                    $notifications[$type],
                    $session->getFlashBag()->get($flashKey)
                );
            }
        }

        return $notifications;
    }

    private function buildModuleViewModel(Module $module): array
    {
        $context = $this->get('prestashop.adapter.legacy.context')->getContext();

        return [
            'name' => $module->displayName,
            'version' => $module->version,
            'donationLink' => 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE',
            'blockAdminLink' => $context->link->getAdminLink('AdminEverBlock', true, [], [
                'configure' => $module->name,
                'module_name' => $module->name,
            ]),
            'faqAdminLink' => $context->link->getAdminLink('AdminEverBlockFaq', true, [], [
                'configure' => $module->name,
                'module_name' => $module->name,
            ]),
            'hookAdminLink' => $context->link->getAdminLink('AdminEverBlockHook', true, [], [
                'configure' => $module->name,
                'module_name' => $module->name,
            ]),
            'shortcodeAdminLink' => $context->link->getAdminLink('AdminEverBlockShortcode', true, [], [
                'configure' => $module->name,
                'module_name' => $module->name,
            ]),
            'modulesListLink' => $context->link->getAdminLink('AdminModules'),
        ];
    }

    private function getExampleTabsUrl(Module $module): string
    {
        $baseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;

        return $baseUrl . 'modules/' . $module->name . '/input/sample/tabs.xlsx';
    }

    private function getConfigurationLink(Module $module): string
    {
        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->get('prestashop.bundle.admin.url_generator');

        return $adminUrlGenerator
            ->setController('AdminModules')
            ->setQueryParams([
                'configure' => $module->name,
                'module_name' => $module->name,
            ])
            ->generateUrl();
    }

    private function presentToolbarButtons(ButtonCollection $collection): array
    {
        return $this->get('prestashop.core.toolbar.presenter')->present($collection);
    }
}
