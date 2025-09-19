<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ImageMapBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $imageMapTemplate = $variables['imageMapTemplate'];

        return [
            [
                            'name' => $module->l('Image map with markers'),
                            'description' => $module->l('Display a clickable map image with markers'),
                            'code' => 'everblock_image_map',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $imageMapTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => '',
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Content'),
                                        'default' => '',
                                    ],
                                    'button_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button text'),
                                        'default' => $module->l('Find a store'),
                                    ],
                                    'button_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button link'),
                                        'default' => '#',
                                    ],
                                    'map_image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Map image'),
                                        'default' => [
                                            'url' => '',
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
                                'name' => 'Marker',
                                'nameFrom' => 'label',
                                'groups' => [
                                    'label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Marker label'),
                                        'default' => '',
                                    ],
                                    'top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Top position (e.g., 50%)'),
                                        'default' => '0%',
                                    ],
                                    'left' => [
                                        'type' => 'text',
                                        'label' => $module->l('Left position (e.g., 50%)'),
                                        'default' => '0%',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
