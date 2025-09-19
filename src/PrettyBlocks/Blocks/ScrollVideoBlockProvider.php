<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ScrollVideoBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $scrollVideoTemplate = $variables['scrollVideoTemplate'];

        return [
            [
                            'name' => $module->l('Scroll video'),
                            'description' => $module->l('Autoplay video when block is visible'),
                            'code' => 'everblock_scroll_video',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $scrollVideoTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Video',
                                'nameFrom' => 'video_url',
                                'groups' => [
                                    'video_url' => [
                                        'type' => 'text',
                                        'label' => $module->l('Video URL'),
                                        'default' => '',
                                    ],
                                    'thumbnail' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Thumbnail image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'width' => [
                                        'type' => 'text',
                                        'label' => $module->l('Video width (e.g., 100% or 400px)'),
                                        'default' => '100%',
                                    ],
                                    'height' => [
                                        'type' => 'text',
                                        'label' => $module->l('Video height (e.g., 360px)'),
                                        'default' => '360px',
                                    ],
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => '',
                                    ],
                                    'content' => [
                                        'type' => 'textarea',
                                        'label' => $module->l('Text'),
                                        'default' => '',
                                    ],
                                    'button_label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button label'),
                                        'default' => '',
                                    ],
                                    'button_url' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button URL'),
                                        'default' => '',
                                    ],
                                    'overlay_color' => [
                                        'type' => 'text',
                                        'label' => $module->l('Overlay color'),
                                        'default' => '#000000',
                                    ],
                                    'overlay_opacity' => [
                                        'type' => 'text',
                                        'label' => $module->l('Overlay opacity (0-1)'),
                                        'default' => '0.5',
                                    ],
                                    'loop' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Infinite loop'),
                                        'default' => false,
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
