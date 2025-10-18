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

namespace Everblock\Tools\Grid\Definition\Factory;

use Context;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class ShortcodeGridDefinitionFactory
{
    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        $columns = [
            'id_everblock_shortcode' => [
                'name' => 'id_everblock_shortcode',
                'label' => $this->trans('ID'),
                'sortable' => true,
                'filterable' => true,
            ],
            'shortcode' => [
                'name' => 'shortcode',
                'label' => $this->trans('Shortcode'),
                'sortable' => true,
                'filterable' => true,
            ],
            'title' => [
                'name' => 'title',
                'label' => $this->trans('Title'),
                'sortable' => true,
                'filterable' => true,
            ],
            'content' => [
                'name' => 'content',
                'label' => $this->trans('Content'),
                'sortable' => false,
                'filterable' => true,
            ],
        ];

        return [
            'columns' => $columns,
            'default_sort_column' => 'id_everblock_shortcode',
            'default_sort_order' => 'desc',
            'row_actions' => [
                'edit' => $this->trans('Edit'),
                'delete' => $this->trans('Delete'),
            ],
            'bulk_actions' => [
                'delete' => [
                    'label' => $this->trans('Delete selected'),
                    'confirm' => $this->trans('Delete selected items?'),
                ],
            ],
        ];
    }

    private function trans(string $message): string
    {
        return Context::getContext()->getTranslator()->trans(
            $message,
            [],
            'Modules.Everblock.Admin'
        );
    }
}
