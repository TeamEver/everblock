<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CategoryProductsBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $categoryProductsTemplate = $variables['categoryProductsTemplate'];
        $defaultLogo = $variables['defaultLogo'];

        return [
            [
                            'name' => $module->l('Category products'),
                            'description' => $module->l('Display products from selected categories'),
                            'code' => 'everblock_category_products',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $categoryProductsTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Category',
                                'nameFrom' => 'category',
                                'groups' => [
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Upload image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'category' => [
                                        'type' => 'selector',
                                        'label' => $module->l('Choose a category'),
                                        'collection' => 'Category',
                                        'selector' => '{id} - {name}',
                                        'default' => '',
                                    ],
                                    'product_limit' => [
                                        'type' => 'text',
                                        'label' => $module->l('Number of products to display'),
                                        'default' => '4',
                                    ],
                                    'include_subcategories' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Include products from subcategories'),
                                        'default' => 0,
                                    ],
                                    'slider' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Enable slider'),
                                        'default' => 0,
                                    ],
                                    'background_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block background color'),
                                    ],
                                    'text_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block text color'),
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
