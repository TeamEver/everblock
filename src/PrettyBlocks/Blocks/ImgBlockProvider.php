<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ImgBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $imgTemplate = $variables['imgTemplate'];

        return [
            [
                            'name' => $module->l('Simple image'),
                            'description' => $module->l('Add simple image'),
                            'code' => 'everblock_img',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $imgTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'slider' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Enable slider'),
                                        'default' => 0,
                                    ],
                                    'slider_items' => [
                                        'type' => 'text',
                                        'label' => $module->l('Number of images in slider'),
                                        'default' => 3,
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => $module->l('Image title'),
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Image title',
                                        'default' => '',
                                    ],
                                    'image_width' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image width (e.g., 100px or 50%)'),
                                        'default' => '100%',
                                    ],
                                    'image_height' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image height (e.g., 100px)'),
                                        'default' => 'auto',
                                    ],
                                    'alt' => [
                                        'type' => 'text',
                                        'label' =>  $module->l('alt attribute'),
                                        'default' => $module->l('My alt attribute')
                                    ],
                                    'url' => [
                                        'type' => 'text',
                                        'label' =>  $module->l('URL'),
                                        'default' =>  $module->l('#')
                                    ],
                                    'banner' => [
                                        'type' => 'fileupload',
                                        'label' => 'Images',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'banner_mobile' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Mobile image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'text_highlight_1' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Highlight text 1'),
                                        'default' => 'protection',
                                    ],
                                    'text_highlight_2' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Highlight text 2'),
                                        'default' => 'santé',
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
