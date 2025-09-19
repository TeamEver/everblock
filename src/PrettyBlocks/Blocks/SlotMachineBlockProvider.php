<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class SlotMachineBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $slotMachineDefaultEndDate = $variables['slotMachineDefaultEndDate'];
        $slotMachineDefaultStartDate = $variables['slotMachineDefaultStartDate'];
        $slotMachineDefaultWinningCombinations = $variables['slotMachineDefaultWinningCombinations'];
        $slotMachineTemplate = $variables['slotMachineTemplate'];

        return [
            [
                            'name' => $module->l('Slot machine'),
                            'description' => $module->l('Interactive slot machine mini game'),
                            'code' => 'everblock_slot_machine',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $slotMachineTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Block title'),
                                        'default' => $module->l('Slot machine'),
                                    ],
                                    'instructions' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Instructions text'),
                                        'default' => $module->l('Press the button to spin the reels and try your luck!'),
                                    ],
                                    'spin_button_label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Spin button label'),
                                        'default' => $module->l('Spin the reels'),
                                    ],
                                    'result_title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Result area title'),
                                        'default' => $module->l('Result'),
                                    ],
                                    'require_login' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Require customers to be logged in to play'),
                                        'default' => true,
                                    ],
                                    'login_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message displayed when login is required'),
                                        'default' => $module->l('Log in to spin the slot machine!'),
                                    ],
                                    'start_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => $slotMachineDefaultStartDate,
                                    ],
                                    'end_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => $slotMachineDefaultEndDate,
                                    ],
                                    'pre_start_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message before the game starts'),
                                        'default' => $module->l('The slot machine opens soon, stay tuned!'),
                                    ],
                                    'post_end_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message after the game ends'),
                                        'default' => $module->l('Thanks for playing! The slot machine is now closed.'),
                                    ],
                                    'winning_combinations' => [
                                        'type' => 'textarea',
                                        'label' => $module->l('Winning combinations (JSON array)'),
                                        'default' => $slotMachineDefaultWinningCombinations,
                                    ],
                                    'default_coupon_name' => [
                                        'type' => 'text',
                                        'label' => $module->l('Default coupon name'),
                                        'default' => $module->l('Slot machine reward'),
                                    ],
                                    'default_coupon_prefix' => [
                                        'type' => 'text',
                                        'label' => $module->l('Default coupon code prefix'),
                                        'default' => 'SLOT',
                                    ],
                                    'default_coupon_validity' => [
                                        'type' => 'number',
                                        'label' => $module->l('Default coupon validity in days'),
                                        'default' => 30,
                                    ],
                                    'default_coupon_type' => [
                                        'type' => 'select',
                                        'label' => $module->l('Default discount type'),
                                        'default' => 'percent',
                                        'choices' => [
                                            'percent' => $module->l('Percent'),
                                            'amount' => $module->l('Amount'),
                                        ],
                                    ],
                                    'default_max_winners' => [
                                        'type' => 'number',
                                        'label' => $module->l('Maximum overall winners (0 for unlimited)'),
                                        'default' => 0,
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
                                'name' => 'Symbol',
                                'nameFrom' => 'label',
                                'groups' => [
                                    'symbol_key' => [
                                        'type' => 'text',
                                        'label' => $module->l('Internal symbol key'),
                                        'default' => 'cherry',
                                    ],
                                    'label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Display label'),
                                        'default' => $module->l('Cherry'),
                                    ],
                                    'probability' => [
                                        'type' => 'text',
                                        'label' => $module->l('Probability'),
                                        'default' => '3',
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Symbol image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'alt_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image alternative text'),
                                        'default' => $module->l('Cherry icon'),
                                    ],
                                    'text' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Accessible description'),
                                        'default' => $module->l('A juicy cherry symbol that hints at the jackpot.'),
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
