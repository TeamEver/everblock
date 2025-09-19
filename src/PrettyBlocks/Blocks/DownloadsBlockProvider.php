<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class DownloadsBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $downloadsTemplate = $variables['downloadsTemplate'];

        return [
            [
                            'name' => $module->l('Downloads list'),
                            'description' => $module->l('Display a list of downloadable resources'),
                            'code' => 'everblock_downloads',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $downloadsTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Block title'),
                                        'default' => $module->l('Downloads'),
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Download',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => $module->l('My file'),
                                    ],
                                    'file' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('File'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'description' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Description'),
                                        'default' => '',
                                    ],
                                    'icon' => [
                                        'type' => 'select',
                                        'label' => $module->l('Select an icon'),
                                        'choices' => EverblockTools::getAvailableSvgIcons(),
                                        'default' => 'file.svg',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
