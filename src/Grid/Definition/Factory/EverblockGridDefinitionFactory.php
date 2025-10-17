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

class EverblockGridDefinitionFactory
{
    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        $columns = [
            'id_everblock' => [
                'name' => 'id_everblock',
                'label' => $this->trans('ID'),
                'sortable' => true,
                'filterable' => true,
            ],
            'name' => [
                'name' => 'name',
                'label' => $this->trans('Name'),
                'sortable' => true,
                'filterable' => true,
            ],
            'hook_name' => [
                'name' => 'hook_name',
                'label' => $this->trans('Hook'),
                'sortable' => true,
                'filterable' => true,
            ],
            'position' => [
                'name' => 'position',
                'label' => $this->trans('Position'),
                'sortable' => true,
                'filterable' => true,
            ],
            'only_home' => [
                'name' => 'only_home',
                'label' => $this->trans('Home only'),
                'type' => 'bool',
                'sortable' => true,
                'filterable' => true,
            ],
            'only_category' => [
                'name' => 'only_category',
                'label' => $this->trans('Category only'),
                'type' => 'bool',
                'sortable' => true,
                'filterable' => true,
            ],
            'only_manufacturer' => [
                'name' => 'only_manufacturer',
                'label' => $this->trans('Manufacturer only'),
                'type' => 'bool',
                'sortable' => true,
                'filterable' => true,
            ],
            'only_supplier' => [
                'name' => 'only_supplier',
                'label' => $this->trans('Supplier only'),
                'type' => 'bool',
                'sortable' => true,
                'filterable' => true,
            ],
            'only_cms_category' => [
                'name' => 'only_cms_category',
                'label' => $this->trans('CMS category only'),
                'type' => 'bool',
                'sortable' => true,
                'filterable' => true,
            ],
            'date_start' => [
                'name' => 'date_start',
                'label' => $this->trans('Date start'),
                'sortable' => true,
                'filterable' => true,
            ],
            'date_end' => [
                'name' => 'date_end',
                'label' => $this->trans('Date end'),
                'sortable' => true,
                'filterable' => true,
            ],
            'modal' => [
                'name' => 'modal',
                'label' => $this->trans('Is modal'),
                'type' => 'bool',
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
        ];

        return [
            'columns' => $columns,
            'default_sort_column' => 'hook_name',
            'default_sort_order' => 'asc',
            'row_actions' => [
                'edit' => $this->trans('Edit'),
                'duplicate' => $this->trans('Duplicate'),
                'toggle' => $this->trans('Toggle status'),
                'export' => $this->trans('Export SQL'),
            ],
            'bulk_actions' => [
                'delete' => [
                    'label' => $this->trans('Delete selected'),
                    'confirm' => $this->trans('Delete selected items?'),
                ],
                'duplicate' => [
                    'label' => $this->trans('Duplicate selected'),
                    'confirm' => $this->trans('Duplicate selected items?'),
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
