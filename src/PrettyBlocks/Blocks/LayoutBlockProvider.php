<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class LayoutBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $layoutTemplate = $variables['layoutTemplate'];

        return [
            [
                            'name' => $module->l('Layout'),
                            'description' => $module->l('Add layout'),
                            'code' => 'everblock_layout',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $layoutTemplate
                            ],
                            'repeater' => [
                                'name' => 'Menu',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Layout title',
                                        'default' => '',
                                    ],
                                    'order' => [
                                        'type' => 'select',
                                        'label' => 'Layout width', 
                                        'default' => 'col-12',
                                        'choices' => [
                                            'col-12' => '100%',
                                            'col-12 col-md-6' => '50%',
                                            'col-12 col-md-4' => '33,33%',
                                            'col-12 col-md-3' => '25%',
                                            'col-12 col-md-2' => '16,67%',
                                        ]
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => 'Layout image',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'image_as_background' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Use image as background'),
                                        'default' => false,
                                    ],
                                    'image_width' => [
                                        'type' => 'text',
                                        'label' => 'Image width (e.g., 100px or 50%)',
                                        'default' => '100%',
                                    ],
                                    'image_height' => [
                                        'type' => 'text',
                                        'label' => 'Image height (e.g., 100px)',
                                        'default' => 'auto',
                                    ],
                                    'parallax' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Parallax mode'),
                                        'default' => false,
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => 'Layout content',
                                        'default' => '[llorem]',
                                    ],
                                    'link' => [
                                        'type' => 'text',
                                        'label' => 'Layout link',
                                        'default' => '',
                                    ],
                                    'obfuscate' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Obfuscate link'),
                                        'default' => '0',
                                    ],
                                    'target_blank' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Open in new tab (only if not obfuscated)'),
                                        'default' => '0',
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
