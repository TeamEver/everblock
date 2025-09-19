<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ModalBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $modalTemplate = $variables['modalTemplate'];

        return [
            [
                            'name' => $module->l('Modal'),
                            'description' => $module->l('Add custom modal'),
                            'code' => 'everblock_modal',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $modalTemplate,
                            ],
                            'repeater' => [
                                'name' => $module->l('Modal title'),
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Modal title',
                                        'default' => '',
                                    ],
                                    'open_name' => [
                                        'type' => 'text',
                                        'label' => 'Open modal button text',
                                        'default' => $module->l('Open'),
                                    ],
                                    'close_name' => [
                                        'type' => 'text',
                                        'label' => 'Close modal button text',
                                        'default' => $module->l('Close'),
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => 'Modal content',
                                        'default' => '[llorem]',
                                    ],
                                    'auto_trigger_modal' => [
                                        'type' => 'radio_group',
                                        'label' => $module->l('Auto trigger modal'),
                                        'default' => 'No',
                                        'choices' => [
                                            '1' => 'No',
                                            '2' => 'Auto',
                                        ]
                                    ],
                                    'modal_title_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Modal title color')
                                    ],
                                    'open_modal_button_bg_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Open modal button background color')
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
