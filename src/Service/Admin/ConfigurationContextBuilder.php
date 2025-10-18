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
use Link;
use Module;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class ConfigurationContextBuilder
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(?Context $context = null)
    {
        $this->context = $context ?? Context::getContext();
    }

    /**
     * @param Module $module
     *
     * @return array<string, mixed>
     */
    public function build(Module $module): array
    {
        $moduleName = $module->name;
        $link = $this->context instanceof Context ? $this->context->link : null;

        $adminLinks = [
            'block_admin_link' => $this->generateAdminLink($link, 'AdminEverBlock', $moduleName),
            'faq_admin_link' => $this->generateAdminLink($link, 'AdminEverBlockFaq', $moduleName),
            'hook_admin_link' => $this->generateAdminLink($link, 'AdminEverBlockHook', $moduleName),
            'shortcode_admin_link' => $this->generateAdminLink($link, 'AdminEverBlockShortcode', $moduleName),
        ];

        $stats = [];
        if (method_exists($module, 'getModuleStatistics')) {
            $stats = $module->getModuleStatistics();
        }

        return array_merge(
            [
                'donation_link' => 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE',
                'module_stats' => $stats,
            ],
            $adminLinks
        );
    }

    private function generateAdminLink(?Link $link, string $controller, string $moduleName): ?string
    {
        if (!$link instanceof Link) {
            return null;
        }

        return $link->getAdminLink($controller, true, [], [
            'configure' => $moduleName,
            'module_name' => $moduleName,
        ]);
    }
}
