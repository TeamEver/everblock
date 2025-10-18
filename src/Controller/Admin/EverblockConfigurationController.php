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

namespace Everblock\Tools\Controller\Admin;

use Everblock\Tools\Service\Admin\ConfigurationContextBuilder;
use Module;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockConfigurationController extends BaseEverblockController
{
    /**
     * @var ConfigurationContextBuilder
     */
    private $configurationContextBuilder;

    public function __construct(
        ConfigurationContextBuilder $configurationContextBuilder,
        ?\Context $context = null,
        ?\PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository $moduleRepository = null,
        ?\Symfony\Contracts\Translation\TranslatorInterface $translator = null,
        ?\Everblock\Tools\Service\Admin\NavigationBuilder $navigationBuilder = null
    ) {
        parent::__construct($context, $moduleRepository, $translator, $navigationBuilder);

        $this->configurationContextBuilder = $configurationContextBuilder;
    }

    public function index(): Response
    {
        $module = $this->resolveModule();

        if (!$module instanceof Module) {
            throw new NotFoundHttpException('Ever Block module instance could not be resolved.');
        }

        $contextData = $this->configurationContextBuilder->build($module);

        $displayUpgrade = method_exists($module, 'checkLatestEverModuleVersion')
            ? (bool) $module->checkLatestEverModuleVersion()
            : false;

        $configurationForm = method_exists($module, 'renderForm')
            ? $module->renderForm()
            : null;

        return $this->renderLayout(
            $this->translate('Configuration'),
            [
                'display_upgrade' => $displayUpgrade,
                'configuration_form' => $configurationForm,
                'admin_links' => [
                    'blocks' => $contextData['block_admin_link'] ?? null,
                    'shortcodes' => $contextData['shortcode_admin_link'] ?? null,
                    'faq' => $contextData['faq_admin_link'] ?? null,
                    'hooks' => $contextData['hook_admin_link'] ?? null,
                ],
            ],
            '@Modules/everblock/templates/admin/everblock/configuration.html.twig',
            [
                'page_identifier' => 'configuration',
                'module_stats' => $contextData['module_stats'] ?? [],
                'donation_link' => $contextData['donation_link'] ?? null,
                'configuration_url' => $this->generateUrl('everblock_admin_configuration'),
            ]
        );
    }
}
