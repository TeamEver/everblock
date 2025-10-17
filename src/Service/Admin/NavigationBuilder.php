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

namespace Everblock\Tools\Service\Admin;

use Context;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class NavigationBuilder
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var UrlGeneratorInterface|null
     */
    private $router;

    /**
     * @var TranslatorInterface|null
     */
    private $translator;

    public function __construct(
        ?Context $context = null,
        ?UrlGeneratorInterface $router = null,
        ?TranslatorInterface $translator = null
    ) {
        $this->context = $context ?? Context::getContext();
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildBreadcrumbs(string $currentLabel): array
    {
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'label' => $this->trans('Ever Block'),
            'url' => $this->generateUrl('everblock_admin_configuration'),
        ];

        if ($currentLabel !== '') {
            $breadcrumbs[] = [
                'label' => $currentLabel,
                'url' => null,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildQuickActions(): array
    {
        $definitions = [
            'blocks' => [
                'route' => 'everblock_admin_blocks',
                'icon' => 'icon-puzzle-piece',
                'title' => $this->trans('Manage blocks'),
                'description' => $this->trans('Create, schedule and personalise content blocks.'),
            ],
            'shortcodes' => [
                'route' => 'everblock_admin_shortcodes',
                'icon' => 'icon-code',
                'title' => $this->trans('Manage shortcodes'),
                'description' => $this->trans('Build reusable snippets for your CMS and product pages.'),
            ],
            'faq' => [
                'route' => 'everblock_admin_faq',
                'icon' => 'icon-question-sign',
                'title' => $this->trans('Manage FAQ'),
                'description' => $this->trans('Update customer-facing answers in a few clicks.'),
            ],
            'hooks' => [
                'route' => 'everblock_admin_hooks',
                'icon' => 'icon-sitemap',
                'title' => $this->trans('Manage hooks'),
                'description' => $this->trans('Control where content appears across your store.'),
            ],
            'configuration' => [
                'route' => 'everblock_admin_configuration',
                'icon' => 'icon-cogs',
                'title' => $this->trans('Module configuration'),
                'description' => $this->trans('Tune integrations, automations and behaviours.'),
            ],
        ];

        $quickActions = [];

        foreach ($definitions as $identifier => $definition) {
            $quickActions[] = [
                'identifier' => $identifier,
                'icon' => $definition['icon'],
                'label' => $definition['title'],
                'description' => $definition['description'],
                'url' => $this->generateUrl($definition['route']),
            ];
        }

        if ($this->getModulesListLink()) {
            $quickActions[] = [
                'identifier' => 'modules',
                'icon' => 'icon-undo',
                'label' => $this->trans('Back to modules'),
                'description' => $this->trans('Return to the module catalogue.'),
                'url' => $this->getModulesListLink(),
            ];
        }

        return $quickActions;
    }

    public function getDonationLink(): ?string
    {
        return 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE';
    }

    public function getModulesListLink(): ?string
    {
        if (!$this->context || !isset($this->context->link)) {
            return null;
        }

        return $this->context->link->getAdminLink('AdminModules');
    }

    private function generateUrl(string $route): ?string
    {
        if (!$this->router instanceof UrlGeneratorInterface) {
            return null;
        }

        try {
            return $this->router->generate($route);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function trans(string $message): string
    {
        if ($this->translator instanceof TranslatorInterface) {
            return $this->translator->trans($message, [], 'Modules.Everblock.Admin');
        }

        return $message;
    }
}
