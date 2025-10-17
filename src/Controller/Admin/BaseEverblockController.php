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

use Context;
use Module;
use Everblock\Tools\Service\Admin\NavigationBuilder;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

abstract class BaseEverblockController extends FrameworkBundleAdminController
{
    protected const MODULE_NAME = 'everblock';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ModuleRepository|null
     */
    protected $moduleRepository;

    /**
     * @var TranslatorInterface|null
     */
    protected $translator;

    /**
     * @var NavigationBuilder|null
     */
    protected $navigationBuilder;

    public function __construct(
        ?Context $context = null,
        ?ModuleRepository $moduleRepository = null,
        ?TranslatorInterface $translator = null,
        ?NavigationBuilder $navigationBuilder = null
    ) {
        $this->context = $context ?? Context::getContext();
        $this->moduleRepository = $moduleRepository;
        $this->translator = $translator;
        $this->navigationBuilder = $navigationBuilder;
    }

    protected function translate(string $message, array $parameters = [], string $domain = 'Modules.Everblock.Admin'): string
    {
        if ($this->translator instanceof TranslatorInterface) {
            return $this->translator->trans($message, $parameters, $domain);
        }

        if (!empty($parameters)) {
            return strtr($message, $parameters);
        }

        return $message;
    }

    protected function renderLayout(
        string $pageTitle,
        array $contentData = [],
        ?string $contentTemplate = null,
        array $overrides = []
    ): Response {
        $module = $this->resolveModule();

        $layoutParameters = array_merge([
            'page_title' => $pageTitle,
            'module_name' => $module ? $module->displayName : $this->translate('Ever Block'),
            'module_version' => $module ? $module->version : null,
            'shortcode_docs' => $module ? ShortcodeDocumentationProvider::getDocumentation($module) : [],
            'quick_actions' => $this->navigationBuilder ? $this->navigationBuilder->buildQuickActions() : [],
            'breadcrumbs' => $this->navigationBuilder ? $this->navigationBuilder->buildBreadcrumbs($pageTitle) : [],
            'donation_link' => $this->navigationBuilder ? $this->navigationBuilder->getDonationLink() : null,
            'modules_list_link' => $this->navigationBuilder ? $this->navigationBuilder->getModulesListLink() : null,
        ], $overrides);

        $layoutParameters['content_template'] = $contentTemplate;
        $layoutParameters['content_data'] = $contentData;

        return $this->render(
            '@Modules/everblock/templates/admin/everblock/layout.html.twig',
            $layoutParameters
        );
    }

    protected function resolveModule(): ?Module
    {
        if ($this->moduleRepository && method_exists($this->moduleRepository, 'getModule')) {
            $repositoryModule = $this->moduleRepository->getModule(static::MODULE_NAME);

            if ($repositoryModule instanceof Module) {
                return $repositoryModule;
            }

            if (is_array($repositoryModule)) {
                if (isset($repositoryModule['instance']) && $repositoryModule['instance'] instanceof Module) {
                    return $repositoryModule['instance'];
                }

                if (isset($repositoryModule['object']) && $repositoryModule['object'] instanceof Module) {
                    return $repositoryModule['object'];
                }
            }
        }

        if (class_exists(Module::class)) {
            return Module::getInstanceByName(static::MODULE_NAME);
        }

        return null;
    }
}
