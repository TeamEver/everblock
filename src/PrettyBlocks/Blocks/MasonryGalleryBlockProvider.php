<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class MasonryGalleryBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $masonryGalleryTemplate = $variables['masonryGalleryTemplate'];

        return [
            [
                            'name' => $module->l('Masonry gallery'),
                            'description' => $module->l('Show masonry style image gallery'),
                            'code' => 'everblock_masonry_gallery',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $masonryGalleryTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Image name',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Image title',
                                        'default' => Configuration::get('PS_SHOP_NAME'),
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => 'Image',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'image_width' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image width (e.g., 100px or 50%)'),
                                        'default' => 'auto',
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
