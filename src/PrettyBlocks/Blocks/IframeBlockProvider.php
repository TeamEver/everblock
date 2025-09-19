<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class IframeBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $iframeTemplate = $variables['iframeTemplate'];

        return [
            [
                            'name' => $module->l('Video iframe'),
                            'description' => $module->l('Add video iframe using embed link'),
                            'code' => 'everblock_iframe',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $iframeTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Video',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'iframe_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('Iframe embed code (like https://www.youtube.com/embed/jfKfPfyJRdk)'),
                                        'default' => 'https://www.youtube.com/embed/jfKfPfyJRdk',
                                    ],
                                    'iframe_source' => [
                                        'type' => 'radio_group',
                                        'label' => $module->l('Iframe source'),
                                        'default' => 'youtube',
                                        'choices' => [
                                             'youtube' => 'youtube',
                                            'vimeo' => 'vimeo',
                                            'dailymotion' => 'dailymotion',
                                            'vidyard' => 'vidyard',
                                        ]
                                    ],
                                    'height' => [
                                        'type' => 'text',
                                        'label' => $module->l('Iframe height (like 250px)'),
                                        'default' => '500px',
                                    ],
                                    'width' => [
                                        'type' => 'text',
                                        'label' => $module->l('Iframe width (like 250px or 50%)'),
                                        'default' => '100%',
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
