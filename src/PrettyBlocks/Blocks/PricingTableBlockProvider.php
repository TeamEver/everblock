<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class PricingTableBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $pricingTableTemplate = $variables['pricingTableTemplate'];

        return [
            [
                            'name' => $module->l('Pricing table'),
                            'description' => $module->l('Display pricing plans in grid or carousel'),
                            'code' => 'everblock_pricing_table',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $pricingTableTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'slider' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Enable slider'),
                                        'default' => 0,
                                    ],
                                    'plans_per_slide' => [
                                        'type' => 'select',
                                        'label' => $module->l('Number of plans per slide'),
                                        'default' => '3',
                                        'choices' => [
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                        ],
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
                            'repeater' => [
                                'name' => 'Plan',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Plan title'),
                                        'default' => '',
                                    ],
                                    'price' => [
                                        'type' => 'text',
                                        'label' => $module->l('Plan price'),
                                        'default' => '',
                                    ],
                                    'features' => [
                                        'type' => 'textarea',
                                        'label' => $module->l('Features'),
                                        'default' => '',
                                    ],
                                    'cta_label' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA label'),
                                        'default' => '',
                                    ],
                                    'cta_url' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA URL'),
                                        'default' => '',
                                    ],
                                    'highlight' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Highlight'),
                                        'default' => 0,
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
