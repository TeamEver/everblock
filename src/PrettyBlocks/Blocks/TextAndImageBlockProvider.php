<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class TextAndImageBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $textAndImageTemplate = $variables['textAndImageTemplate'];

        return [
            [
                            'name' => $module->l('Text and image'),
                            'description' => $module->l('Add image and text layout'),
                            'code' => 'everblock_text_and_image',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $textAndImageTemplate
                            ],
                            'repeater' => [
                                'name' => 'Menu',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Block title',
                                        'default' => '',
                                    ],
                                    'order' => [
                                        'type' => 'select',
                                        'label' => 'Block order', 
                                        'default' => '1',
                                        'choices' => [
                                            '1' => 'First image, then text',
                                            '2' => 'First text, then image',
                                        ]
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => 'Layout image',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'image_mobile' => [
                                        'type' => 'fileupload',
                                        'label' => 'Mobile layout image',
                                        'default' => [
                                            'url' => '',
                                        ],
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
                                    'text_position_desktop' => [
                                        'type' => 'select',
                                        'label' => $module->l('Text position (desktop)'),
                                        'choices' => [
                                            'start' => $module->l('Top'),
                                            'center' => $module->l('Center'),
                                            'end' => $module->l('Bottom'),
                                        ],
                                        'default' => 'center',
                                    ],
                                    'text_position_mobile' => [
                                        'type' => 'select',
                                        'label' => $module->l('Text position (mobile)'),
                                        'choices' => [
                                            'start' => $module->l('Top'),
                                            'center' => $module->l('Center'),
                                            'end' => $module->l('Bottom'),
                                        ],
                                        'default' => 'center',
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
