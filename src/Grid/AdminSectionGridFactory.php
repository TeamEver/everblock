<?php

declare(strict_types=1);

namespace Everblock\Tools\Grid;

use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\ViewOptionsCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinition;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Grid;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteria;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

final class AdminSectionGridFactory
{
    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
    }

    public function build(string $section, array $config, array $rows): Grid
    {
        $columns = new ColumnCollection();
        foreach ($config['columns'] as $column) {
            $columns->add(
                (new DataColumn($column))
                    ->setName($this->humanize($column))
                    ->setOptions([
                        'field' => $column,
                        'max_displayed_characters' => 120,
                    ])
            );
        }

        $rowActions = (new RowActionCollection())
            ->add(
                (new LinkRowAction('edit'))
                    ->setName('Edit')
                    ->setIcon('edit')
                    ->setOptions([
                        'route' => $config['route'] . '_edit',
                        'route_param_name' => 'id',
                        'route_param_field' => $config['id'],
                    ])
            )
            ->add(
                (new LinkRowAction('delete'))
                    ->setName('Delete')
                    ->setIcon('delete')
                    ->setOptions([
                        'route' => $config['route'] . '_delete',
                        'route_param_name' => 'id',
                        'route_param_field' => $config['id'],
                        'confirm_message' => 'Delete this item?',
                    ])
            );

        if ($section === 'blocks') {
            $rowActions
                ->add(
                    (new LinkRowAction('toggle'))
                        ->setName('Enable / disable')
                        ->setIcon('power_settings_new')
                        ->setOptions([
                            'route' => 'admin_everblock_blocks_toggle',
                            'route_param_name' => 'id',
                            'route_param_field' => $config['id'],
                        ])
                )
                ->add(
                    (new LinkRowAction('duplicate'))
                    ->setName('Duplicate')
                    ->setIcon('content_copy')
                    ->setOptions([
                        'route' => 'admin_everblock_blocks_duplicate',
                        'route_param_name' => 'id',
                        'route_param_field' => $config['id'],
                    ])
                );
        }

        $columns->add(
            (new ActionColumn('actions'))
                ->setName('Actions')
                ->setOptions(['actions' => $rowActions])
        );

        $bulkActions = new BulkActionCollection();
        if ($section === 'blocks') {
            $bulkActions
                ->add(
                    (new SubmitBulkAction('enable_selection'))
                        ->setName('Enable selected')
                        ->setOptions(['submit_route' => 'admin_everblock_blocks_bulk_enable'])
                )
                ->add(
                    (new SubmitBulkAction('disable_selection'))
                        ->setName('Disable selected')
                        ->setOptions(['submit_route' => 'admin_everblock_blocks_bulk_disable'])
                )
                ->add(
                    (new SubmitBulkAction('duplicate_selection'))
                        ->setName('Duplicate selected')
                        ->setOptions(['submit_route' => 'admin_everblock_blocks_bulk_duplicate'])
                )
                ->add(
                    (new SubmitBulkAction('delete_selection'))
                        ->setName('Delete selected')
                        ->setOptions([
                            'submit_route' => 'admin_everblock_blocks_bulk_delete',
                            'confirm_message' => 'Delete selected blocks?',
                        ])
                );
        }

        $definition = new GridDefinition(
            'everblock_' . $section,
            (string) $config['title'],
            $columns,
            new FilterCollection(),
            new GridActionCollection(),
            $bulkActions,
            new ViewOptionsCollection()
        );

        return new Grid(
            $definition,
            new GridData(new \PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection($rows), count($rows)),
            new SearchCriteria(),
            $this->formFactory->createNamed($definition->getId(), FormType::class, [])
        );
    }

    private function humanize(string $column): string
    {
        return ucwords(str_replace('_', ' ', $column));
    }
}
