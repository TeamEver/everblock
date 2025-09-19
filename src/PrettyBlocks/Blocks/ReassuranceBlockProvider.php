<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ReassuranceBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $reassuranceTemplate = $variables['reassuranceTemplate'];

        return [
            [
                            'name' => $module->l('Reassurance block'),
                            'description' => $module->l('Add multiple reassurance icons with titles and short texts.'),
                            'code' => 'everblock_reassurance',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $reassuranceTemplate,
                            ],
                            'settings' => [
                                'default' => [
                                    'display_inline' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Display reassurances side by side'),
                                        'default' => false,
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Reassurances',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Upload image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'icon' => [
                                        'type' => 'select',
                                        'label' => $module->l('Select an icon'),
                                        'choices' => EverblockTools::getAvailableSvgIcons(),
                                        'default' => 'payment.svg',
                                    ],
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => $module->l('Free delivery'),
                                    ],
                                    'text' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Short text'),
                                        'default' => $module->l('On all orders over 50€'),
                                    ],
                                    'background_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block background color')
                                    ],
                                    'text_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block text color')
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
