<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class WheelOfFortuneBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $wheelTemplate = $variables['wheelTemplate'];

        return [
            [
                            'name' => $module->l('Wheel of fortune'),
                            'description' => $module->l('Display a prize wheel'),
                            'code' => 'everblock_wheel_of_fortune',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $wheelTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Block title'),
                                        'default' => $module->l('Wheel of fortune'),
                                    ],
                                    'button_label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button label'),
                                        'default' => $module->l('Spin'),
                                    ],
                                    'login_text' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Text above login form'),
                                        'default' => '',
                                    ],
                                    'top_text' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Text above the wheel'),
                                        'default' => '',
                                    ],
                                    'bottom_text' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Text below the wheel'),
                                        'default' => '',
                                    ],
                                    'start_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => '',
                                    ],
                                    'end_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => '',
                                    ],
                                    'pre_start_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message before the game starts'),
                                        'default' => '',
                                    ],
                                    'post_end_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message after the game ends'),
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
                            'repeater' => [
                                'name' => 'Segment',
                                'nameFrom' => 'label',
                                'groups' => [
                                    'label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Label'),
                                        'default' => '',
                                    ],
                                    'probability' => [
                                        'type' => 'text',
                                        'label' => $module->l('Probability'),
                                        'default' => '1',
                                    ],
                                    'color' => [
                                        'type' => 'color',
                                        'label' => $module->l('Color'),
                                        'default' => '#ff0000',
                                    ],
                                    'text_color' => [
                                        'type' => 'color',
                                        'label' => $module->l('Text color'),
                                        'default' => '#ffffff',
                                    ],
                                    'discount' => [
                                        'type' => 'text',
                                        'label' => $module->l('Discount value'),
                                        'default' => '10',
                                    ],
                                    'coupon_name' => [
                                        'type' => 'text',
                                        'label' => $module->l('Coupon name'),
                                        'default' => $module->l('Wheel reward'),
                                    ],
                                    'coupon_prefix' => [
                                        'type' => 'text',
                                        'label' => $module->l('Coupon code prefix'),
                                        'default' => 'WHEEL',
                                    ],
                                    'coupon_validity' => [
                                        'type' => 'number',
                                        'label' => $module->l('Coupon validity in days'),
                                        'default' => '30',
                                    ],
                                    'minimum_purchase' => [
                                        'type' => 'text',
                                        'label' => $module->l('Minimum purchase amount (tax incl.)'),
                                        'default' => '',
                                    ],
                                    'max_winners' => [
                                        'type' => 'number',
                                        'label' => $module->l('Maximum winners for this segment (0 for unlimited)'),
                                        'default' => '0',
                                    ],
                                    'coupon_type' => [
                                        'type' => 'select',
                                        'label' => $module->l('Discount type'),
                                        'default' => 'percent',
                                        'choices' => [
                                            'percent' => $module->l('Percent'),
                                            'amount' => $module->l('Amount'),
                                        ],
                                    ],
                                    'id_categories' => [
                                        'type' => 'selector',
                                        'label' => $module->l('Coupon category restrictions'),
                                        'collection' => 'Category',
                                        'selector' => '{id} - {name}',
                                        'multiple' => true,
                                        'default' => [],
                                    ],
                                    'isWinning' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Winning segment'),
                                        'default' => false,
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
