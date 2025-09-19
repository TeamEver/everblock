<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CategoryPriceBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $categoryPriceTemplate = $variables['categoryPriceTemplate'];
        $defaultLogo = $variables['defaultLogo'];
        $context = $definitionContext->getContext();

        return [
            [
                            'name' => $module->l('Category price list'),
                            'description' => $module->l('Display categories with starting price'),
                            'code' => 'everblock_category_price',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $categoryPriceTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Category',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'category' => [
                                        'type' => 'selector',
                                        'label' => $module->l('Category'),
                                        'collection' => 'Category',
                                        'selector' => '{id} - {name}',
                                        'default' => \HelperBuilder::getRandomCategory((int) $context->language->id, (int) $context->shop->id),
                                        'force_default_value' => true,
                                    ],
                                    'name' => [
                                        'type' => 'text',
                                        'label' => $module->l('Custom title'),
                                        'default' => '',
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Custom image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'css_class' => [
                                        'type' => 'text',
                                        'label' => $module->l('Custom CSS class'),
                                        'default' => '',
                                    ],
                                    'padding_left' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'padding_right' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'padding_top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'padding_bottom' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_left' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_right' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_bottom' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
