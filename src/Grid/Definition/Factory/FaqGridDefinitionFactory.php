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

class FaqGridDefinitionFactory
{
    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        $columns = [
            'id_everblock_faq' => [
                'name' => 'id_everblock_faq',
                'label' => $this->trans('ID'),
                'sortable' => true,
                'filterable' => true,
            ],
            'tag_name' => [
                'name' => 'tag_name',
                'label' => $this->trans('Tag'),
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
            'position' => [
                'name' => 'position',
                'label' => $this->trans('Position'),
                'sortable' => true,
                'filterable' => true,
            ],
            'active' => [
                'name' => 'active',
                'label' => $this->trans('Status'),
                'type' => 'bool',
                'sortable' => true,
                'filterable' => true,
            ],
            'date_add' => [
                'name' => 'date_add',
                'label' => $this->trans('Created at'),
                'sortable' => true,
                'filterable' => true,
            ],
            'date_upd' => [
                'name' => 'date_upd',
                'label' => $this->trans('Updated at'),
                'sortable' => true,
                'filterable' => true,
            ],
        ];

        return [
            'columns' => $columns,
            'default_sort_column' => 'id_everblock_faq',
            'default_sort_order' => 'desc',
            'row_actions' => [
                'edit' => $this->trans('Edit'),
                'delete' => $this->trans('Delete'),
                'duplicate' => $this->trans('Duplicate'),
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
