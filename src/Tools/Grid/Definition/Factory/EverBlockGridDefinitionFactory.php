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
 */

namespace Everblock\Tools\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\SimpleGridAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BulkActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Type\Common\ChoiceType;
use PrestaShop\PrestaShop\Core\Grid\Filter\Type\Common\DateRangeType;
use PrestaShop\PrestaShop\Core\Grid\Filter\Type\Common\TextType;

class EverBlockGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    private const GRID_ID = 'ever_block';

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Ever Block', [], 'Modules.Everblock.Admin');
    }

    protected function getColumns()
    {
        $columns = new ColumnCollection();

        $columns
            ->add((new BulkActionColumn('bulk'))
                ->setOptions([
                    'bulk_field' => 'id_everblock',
                ])
            )
            ->add((new DataColumn('id_everblock'))
                ->setName($this->trans('ID', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'id_everblock',
                ])
            )
            ->add((new DataColumn('name'))
                ->setName($this->trans('Name', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'name',
                ])
            )
            ->add((new DataColumn('hname'))
                ->setName($this->trans('Hook', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'hname',
                ])
            )
            ->add((new DataColumn('position'))
                ->setName($this->trans('Position', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'position',
                ])
            )
            ->add((new DataColumn('only_home'))
                ->setName($this->trans('Home only', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'only_home',
                    'is_bool' => true,
                ])
            )
            ->add((new DataColumn('only_category'))
                ->setName($this->trans('Category only', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'only_category',
                    'is_bool' => true,
                ])
            )
            ->add((new DataColumn('only_manufacturer'))
                ->setName($this->trans('Manufacturer only', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'only_manufacturer',
                    'is_bool' => true,
                ])
            )
            ->add((new DataColumn('only_supplier'))
                ->setName($this->trans('Supplier only', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'only_supplier',
                    'is_bool' => true,
                ])
            )
            ->add((new DataColumn('only_cms_category'))
                ->setName($this->trans('CMS category only', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'only_cms_category',
                    'is_bool' => true,
                ])
            )
            ->add((new DataColumn('date_start'))
                ->setName($this->trans('Date start', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'date_start',
                ])
            )
            ->add((new DataColumn('date_end'))
                ->setName($this->trans('Date end', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'date_end',
                ])
            )
            ->add((new DataColumn('modal'))
                ->setName($this->trans('Is modal', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'field' => 'modal',
                    'is_bool' => true,
                ])
            )
            ->add((new ToggleColumn('active'))
                ->setName($this->trans('Status', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'active',
                    'primary_field' => 'id_everblock',
                    'route' => 'admin_everblock_toggle_status',
                    'route_param_name' => 'everBlockId',
                ])
            );

        return $columns;
    }

    protected function getFilters()
    {
        $filters = new FilterCollection();

        $filters
            ->add((new Filter('id_everblock', TextType::class))
                ->setTypeOptions([
                    'attr' => [
                        'placeholder' => $this->trans('Search ID', [], 'Admin.Global'),
                    ],
                ])
                ->setAssociatedColumn('id_everblock')
            )
            ->add((new Filter('name', TextType::class))
                ->setAssociatedColumn('name')
            )
            ->add((new Filter('hname', TextType::class))
                ->setAssociatedColumn('hname')
            )
            ->add((new Filter('only_home', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('only_home')
            )
            ->add((new Filter('only_category', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('only_category')
            )
            ->add((new Filter('only_manufacturer', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('only_manufacturer')
            )
            ->add((new Filter('only_supplier', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('only_supplier')
            )
            ->add((new Filter('only_cms_category', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('only_cms_category')
            )
            ->add((new Filter('modal', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('modal')
            )
            ->add((new Filter('active', ChoiceType::class))
                ->setTypeOptions([
                    'choices' => $this->getBooleanChoices(),
                ])
                ->setAssociatedColumn('active')
            )
            ->add((new Filter('date_start', DateRangeType::class))
                ->setAssociatedColumn('date_start')
            )
            ->add((new Filter('date_end', DateRangeType::class))
                ->setAssociatedColumn('date_end')
            );

        return $filters;
    }

    protected function getGridActions()
    {
        $collection = new GridActionCollection();
        $collection->add(
            (new SimpleGridAction('create'))
                ->setName($this->trans('Add new block', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'route' => 'admin_everblock_create',
                    'icon' => 'add_circle',
                ])
        );

        return $collection;
    }

    protected function getRowActions()
    {
        $actions = new RowActionCollection();

        $actions
            ->add((new LinkRowAction('edit'))
                ->setName($this->trans('Edit', [], 'Admin.Actions'))
                ->setIcon('edit')
                ->setOptions([
                    'route' => 'admin_everblock_edit',
                    'route_param_name' => 'everBlockId',
                    'route_param_field' => 'id_everblock',
                ])
            )
            ->add((new SubmitRowAction('delete'))
                ->setName($this->trans('Delete', [], 'Admin.Actions'))
                ->setIcon('delete')
                ->setOptions([
                    'route' => 'admin_everblock_delete',
                    'route_param_name' => 'everBlockId',
                    'route_param_field' => 'id_everblock',
                    'confirm_message' => $this->trans('Delete selected block?', [], 'Modules.Everblock.Admin'),
                ])
            )
            ->add((new SubmitRowAction('duplicate'))
                ->setName($this->trans('Duplicate', [], 'Admin.Actions'))
                ->setIcon('content_copy')
                ->setOptions([
                    'route' => 'admin_everblock_duplicate',
                    'route_param_name' => 'everBlockId',
                    'route_param_field' => 'id_everblock',
                ])
            )
            ->add((new LinkRowAction('export'))
                ->setName($this->trans('Export SQL', [], 'Modules.Everblock.Admin'))
                ->setIcon('file_download')
                ->setOptions([
                    'route' => 'admin_everblock_export',
                    'route_param_name' => 'everBlockId',
                    'route_param_field' => 'id_everblock',
                    'new_tab' => true,
                ])
            );

        return $actions;
    }

    protected function getBulkActions()
    {
        $bulkActions = new BulkActionCollection();

        $bulkActions
            ->add((new SubmitBulkAction('duplicate_all'))
                ->setName($this->trans('Duplicate selection', [], 'Modules.Everblock.Admin'))
                ->setOptions([
                    'submit_action' => 'duplicate',
                    'route' => 'admin_everblock_bulk',
                    'confirm_message' => $this->trans('Duplicate selected blocks?', [], 'Modules.Everblock.Admin'),
                ])
            )
            ->add((new SubmitBulkAction('delete_selection'))
                ->setName($this->trans('Delete selection', [], 'Admin.Actions'))
                ->setOptions([
                    'submit_action' => 'delete',
                    'route' => 'admin_everblock_bulk',
                    'confirm_message' => $this->trans('Delete selected blocks?', [], 'Modules.Everblock.Admin'),
                ])
            )
            ->add((new SubmitBulkAction('enable_selection'))
                ->setName($this->trans('Enable selection', [], 'Admin.Actions'))
                ->setOptions([
                    'submit_action' => 'enable',
                    'route' => 'admin_everblock_bulk',
                ])
            )
            ->add((new SubmitBulkAction('disable_selection'))
                ->setName($this->trans('Disable selection', [], 'Admin.Actions'))
                ->setOptions([
                    'submit_action' => 'disable',
                    'route' => 'admin_everblock_bulk',
                ])
            );

        return $bulkActions;
    }

    private function getBooleanChoices()
    {
        return [
            $this->trans('Yes', [], 'Admin.Global') => 1,
            $this->trans('No', [], 'Admin.Global') => 0,
        ];
    }
}
