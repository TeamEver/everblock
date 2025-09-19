<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ImgSliderBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $imgSliderTemplate = $variables['imgSliderTemplate'];

        return [
            [
                            'name' => $module->l('Images slider'),
                            'description' => $module->l('Show images slider (images must have same size)'),
                            'code' => 'everblock_img_slider',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $imgSliderTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Image',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => 'Layout image',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Image title',
                                        'default' => Configuration::get('PS_SHOP_NAME'),
                                    ],
                                    'link' => [
                                        'type' => 'text',
                                        'label' => 'Image link',
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
