<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ButtonBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $buttonTemplate = $variables['buttonTemplate'];
        $defaultLogo = $variables['defaultLogo'];

        return [
            [
                            'name' => $module->l('Button'),
                            'description' => $module->l('Add simple button'),
                            'code' => 'everblock_button',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $buttonTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Tab',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Button title',
                                        'default' => Configuration::get('PS_SHOP_NAME'),
                                    ],
                                    'button_type' => [
                                        'type' => 'radio_group', // type of field
                                        'label' => $module->l('Button type'), // label to display
                                        'default' => 'primary', // default value (String)
                                        'choices' => [
                                            'primary' => 'primary',
                                            'secondary' => 'secondary',
                                            'success' => 'success',
                                            'danger' => 'danger',
                                            'warning' => 'warning',
                                            'info' => 'info',
                                            'light' => 'light',
                                            'dark' => 'dark',
                                        ]
                                    ],
                                    'button_content' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button text'),
                                        'default' => '',
                                    ],
                                    'button_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button link'),
                                        'default' => '',
                                    ],
                                    'color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '#fff',
                                        'label' => $module->l('Button text color')
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
