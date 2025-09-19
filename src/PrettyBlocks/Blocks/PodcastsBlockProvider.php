<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class PodcastsBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $podcastsTemplate = $variables['podcastsTemplate'];

        return [
            [
                            'name' => $module->l('Podcasts'),
                            'description' => $module->l('Display podcasts'),
                            'code' => 'everblock_podcasts',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $podcastsTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Block title'),
                                        'default' => $module->l('Podcasts'),
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Podcast',
                                'nameFrom' => 'episode_title',
                                'groups' => [
                                    'cover_image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Cover image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'episode_title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Episode title'),
                                        'default' => '',
                                    ],
                                    'audio_url' => [
                                        'type' => 'text',
                                        'label' => $module->l('Audio URL'),
                                        'default' => '',
                                    ],
                                    'duration' => [
                                        'type' => 'text',
                                        'label' => $module->l('Duration'),
                                        'default' => '',
                                    ],
                                    'description' => [
                                        'type' => 'textarea',
                                        'label' => $module->l('Description'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
