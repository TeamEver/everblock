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

namespace Everblock\PrestaShopBundle\Grid\Definition\Factory;

use Everblock\PrestaShopBundle\Grid\Search\Filters\EverBlockFilters;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShopBundle\Form\Admin\Type\YesAndNoChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EverBlockGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    protected function getId(): string
    {
        return EverBlockFilters::FILTER_ID;
    }

    protected function getName(): string
    {
        return $this->trans('HTML blocks', 'Modules.Everblock.Admin');
    }

    protected function getColumns(): ColumnCollection
    {
        $columns = new ColumnCollection();

        $columns->add((new DataColumn('id_everblock'))
            ->setName($this->trans('ID', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'id_everblock',
            ])
        );

        $columns->add((new DataColumn('name'))
            ->setName($this->trans('Name', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'name',
            ])
        );

        $columns->add((new DataColumn('hname'))
            ->setName($this->trans('Hook', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'hname',
            ])
        );

        $columns->add((new DataColumn('position'))
            ->setName($this->trans('Position', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'position',
            ])
        );

        foreach ([
            'only_home' => $this->trans('Home only', 'Modules.Everblock.Admin'),
            'only_category' => $this->trans('Category only', 'Modules.Everblock.Admin'),
            'only_manufacturer' => $this->trans('Manufacturer only', 'Modules.Everblock.Admin'),
            'only_supplier' => $this->trans('Supplier only', 'Modules.Everblock.Admin'),
            'only_cms_category' => $this->trans('CMS category only', 'Modules.Everblock.Admin'),
            'modal' => $this->trans('Is modal', 'Modules.Everblock.Admin'),
        ] as $field => $label) {
            $columns->add((new DataColumn($field))
                ->setName($label)
                ->setOptions([
                    'field' => $field,
                ])
            );
        }

        $columns->add((new ToggleColumn('active'))
            ->setName($this->trans('Status', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'active',
                'primary_field' => 'id_everblock',
                'route' => 'admin_everblock_toggle_status',
                'route_param_name' => 'everBlockId',
                'route_param_field' => 'id_everblock',
            ])
        );

        $columns->add((new DataColumn('date_start'))
            ->setName($this->trans('Date start', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'date_start',
            ])
        );

        $columns->add((new DataColumn('date_end'))
            ->setName($this->trans('Date end', 'Modules.Everblock.Admin'))
            ->setOptions([
                'field' => 'date_end',
            ])
        );

        return $columns;
    }

    protected function getFilters(): FilterCollection
    {
        $filters = new FilterCollection();

        foreach ([
            'id_everblock' => $this->trans('Search ID', 'Modules.Everblock.Admin'),
            'name' => $this->trans('Search name', 'Modules.Everblock.Admin'),
            'hname' => $this->trans('Search hook', 'Modules.Everblock.Admin'),
            'position' => $this->trans('Search position', 'Modules.Everblock.Admin'),
            'date_start' => $this->trans('Search start date', 'Modules.Everblock.Admin'),
            'date_end' => $this->trans('Search end date', 'Modules.Everblock.Admin'),
        ] as $filterName => $placeholder) {
            $filters->add((new Filter($filterName, TextType::class))
                ->setTypeOptions([
                    'attr' => [
                        'placeholder' => $placeholder,
                    ],
                ])
            );
        }

        foreach ([
            'only_home',
            'only_category',
            'only_manufacturer',
            'only_supplier',
            'only_cms_category',
            'modal',
            'active',
        ] as $booleanFilter) {
            $filters->add((new Filter($booleanFilter, YesAndNoChoiceType::class))
                ->setAssociatedColumn($booleanFilter)
            );
        }

        return $filters;
    }

    protected function getRowActions(): RowActionCollection
    {
        $rowActions = new RowActionCollection();

        $rowActions->add((new SubmitRowAction('duplicate'))
            ->setName($this->trans('Duplicate', 'Modules.Everblock.Admin'))
            ->setIcon('content_copy')
            ->setOptions([
                'route' => 'admin_everblock_duplicate',
                'route_param_name' => 'everBlockId',
                'route_param_field' => 'id_everblock',
                'confirm_message' => $this->trans('Duplicate this block?', 'Modules.Everblock.Admin'),
            ])
        );

        $rowActions->add((new LinkRowAction('export'))
            ->setName($this->trans('Export SQL', 'Modules.Everblock.Admin'))
            ->setIcon('cloud_download')
            ->setOptions([
                'route' => 'admin_everblock_export_sql',
                'route_param_name' => 'everBlockId',
                'route_param_field' => 'id_everblock',
            ])
        );

        $rowActions->add((new SubmitRowAction('delete'))
            ->setName($this->trans('Delete', 'Modules.Everblock.Admin'))
            ->setIcon('delete')
            ->setOptions([
                'route' => 'admin_everblock_delete',
                'route_param_name' => 'everBlockId',
                'route_param_field' => 'id_everblock',
                'confirm_message' => $this->trans('Delete this block?', 'Modules.Everblock.Admin'),
            ])
        );

        return $rowActions;
    }

    protected function getBulkActions(): BulkActionCollection
    {
        $bulkActions = new BulkActionCollection();

        $bulkActions->add((new SubmitBulkAction('delete'))
            ->setName($this->trans('Delete selected', 'Modules.Everblock.Admin'))
            ->setOptions([
                'submit_route' => 'admin_everblock_bulk_delete',
                'confirm_message' => $this->trans('Delete selected blocks?', 'Modules.Everblock.Admin'),
            ])
        );

        $bulkActions->add((new SubmitBulkAction('disable'))
            ->setName($this->trans('Disable selected', 'Modules.Everblock.Admin'))
            ->setOptions([
                'submit_route' => 'admin_everblock_bulk_disable',
            ])
        );

        $bulkActions->add((new SubmitBulkAction('enable'))
            ->setName($this->trans('Enable selected', 'Modules.Everblock.Admin'))
            ->setOptions([
                'submit_route' => 'admin_everblock_bulk_enable',
            ])
        );

        $bulkActions->add((new SubmitBulkAction('duplicate'))
            ->setName($this->trans('Duplicate selected', 'Modules.Everblock.Admin'))
            ->setOptions([
                'submit_route' => 'admin_everblock_bulk_duplicate',
            ])
        );

        return $bulkActions;
    }
}
