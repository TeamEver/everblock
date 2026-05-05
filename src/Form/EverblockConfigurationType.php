<?php

declare(strict_types=1);

namespace Everblock\Tools\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EverblockConfigurationType extends AbstractType
{
    public static function tabs(bool $hasStores): array
    {
        $tabs = [
            'settings' => 'Réglages',
            'meta_tools' => 'Meta Tools',
            'wordpress_tools' => 'WordPress Tools',
            'google_maps' => 'Google Tools',
            'migration' => 'Migration des URL',
            'tools' => 'Outils',
            'files' => 'Gestionnaire de fichiers',
            'flags' => 'Flags',
            'pages' => 'Pages',
        ];

        if ($hasStores) {
            $tabs['holiday'] = 'Holiday opening hours by store';
        }

        $tabs['cron'] = 'Tâches crons';

        return $tabs;
    }

    public static function fieldTabs(array $languages, array $bannedFeatures, array $stores, array $holidays, bool $hasInstagramToken): array
    {
        $fieldTabs = [
            'settings' => [
                'EVEROPTIONS_POSITION',
                'EVERBLOCK_MAINTENANCE_PSSWD',
                'EVERPSCSS_CACHE',
                'EVERBLOCK_CACHE',
                'EVERBLOCK_LOAD_FRONT_CSS',
                'EVERBLOCK_USE_OBF',
                'EVERBLOCK_TINYMCE',
                'EVERBLOCK_DISABLE_WEBP',
                'EVERPS_DUMMY_NBR',
                'EVERPSCSS_P_LLOREM_NUMBER',
                'EVERPSCSS_S_LLOREM_NUMBER',
                'EVERPS_TAB_NB',
            ],
            'meta_tools' => [
                'EVERINSTA_ACCESS_TOKEN',
            ],
            'wordpress_tools' => [
                'EVERWP_API_URL',
                'EVERWP_BLOG_URL',
                'EVERWP_POST_NBR',
                'EVERWP_POSTS_BG_IMAGE',
            ],
            'google_maps' => [
                'EVERBLOCK_GOOGLE_API_KEY',
                'EVERBLOCK_GOOGLE_PLACE_ID',
                'EVERBLOCK_GOOGLE_REVIEWS_LIMIT',
                'EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING',
                'EVERBLOCK_GOOGLE_REVIEWS_SORT',
                'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING',
                'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR',
                'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA',
                'EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL',
                'EVERBLOCK_GOOGLE_REVIEWS_CTA_URL',
                'EVERBLOCK_GMAP_KEY',
                'EVERBLOCK_MARKER_ICON',
                'EVERBLOCK_STORELOCATOR_TOGGLE',
            ],
            'migration' => [
                'EVERPS_OLD_URL',
                'EVERPS_NEW_URL',
            ],
            'tools' => [
                'EVERPSCSS',
                'EVERPSJS',
                'EVERPSCSS_LINKS',
                'EVERPSJS_LINKS',
                'EVERPS_HEADER_SCRIPTS',
            ],
            'files' => [
                'TABS_FILE',
            ],
            'flags' => [
                'EVERBLOCK_SOLDOUT_FLAG',
                'EVERPS_FEATURES_AS_FLAGS',
                'EVERPS_FLAG_NB',
                'EVER_SOLDOUT_COLOR',
                'EVER_SOLDOUT_TEXTCOLOR',
            ],
            'pages' => [
                'EVERBLOCK_PAGES_BASE_URL',
                'EVERBLOCK_PAGES_PER_PAGE',
                'EVERBLOCK_FAQ_BASE_URL',
                'EVERBLOCK_FAQ_PER_PAGE',
            ],
            'holiday' => [],
            'cron' => [],
        ];

        foreach ($languages as $language) {
            $langId = (int) $language['id_lang'];
            array_unshift($fieldTabs['settings'], 'EVEROPTIONS_TITLE_' . $langId);
            $fieldTabs['settings'][] = 'EVER_TAB_TITLE_' . $langId;
            $fieldTabs['settings'][] = 'EVER_TAB_CONTENT_' . $langId;
        }

        if ($hasInstagramToken) {
            $fieldTabs['meta_tools'][] = 'EVERINSTA_LINK';
            $fieldTabs['meta_tools'][] = 'EVERINSTA_SHOW_CAPTION';
        }

        foreach ($bannedFeatures as $featureId) {
            $featureId = (int) $featureId;
            $fieldTabs['flags'][] = 'EVERPS_FEATURE_COLOR_' . $featureId;
            $fieldTabs['flags'][] = 'EVERPS_FEATURE_TEXTCOLOR_' . $featureId;
        }

        foreach ($stores as $store) {
            foreach ($holidays as $date) {
                $fieldTabs['holiday'][] = 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date;
            }
        }

        return $fieldTabs;
    }

    public static function actionButtons(): array
    {
        return [
            'settings' => [
                ['name' => 'submitCreateProduct', 'title' => 'Create fake products', 'icon' => 'auto_fix_high'],
            ],
            'tools' => [
                ['name' => 'submitEmptyCache', 'title' => 'Empty cache', 'icon' => 'cached'],
                ['name' => 'submitEmptyLogs', 'title' => 'Empty logs', 'icon' => 'delete_sweep'],
                ['name' => 'submitDropUnusedLangs', 'title' => 'Drop unused langs', 'icon' => 'translate'],
                ['name' => 'submitSecureModuleFoldersWithApache', 'title' => 'Secure all modules folders using Apache', 'icon' => 'security'],
                ['name' => 'submitBackupBlocks', 'title' => 'Backup all blocks', 'icon' => 'download'],
                ['name' => 'submitRestoreBackup', 'title' => 'Restore backup', 'icon' => 'restore'],
            ],
            'migration' => [
                ['name' => 'submitMigrateUrls', 'title' => 'Migrate URLS', 'icon' => 'sync_alt'],
            ],
            'files' => [
                ['name' => 'submitUploadTabsFile', 'title' => 'Upload file', 'icon' => 'upload_file'],
            ],
        ];
    }

    public static function docs(): array
    {
        return [
            'settings' => 'Configure global behavior: checkout step title, cache, editor, product tabs and generated content defaults.',
            'meta_tools' => 'Configure Meta integrations, including Instagram access and display options.',
            'wordpress_tools' => 'Configure the WordPress REST endpoint and the latest posts block.',
            'google_maps' => 'Configure Google Places reviews, Google Maps keys and store locator marker options.',
            'migration' => 'Replace old URLs with new URLs in shop content for migration work.',
            'tools' => 'Run maintenance tools such as cache cleanup, log cleanup, backups and restores.',
            'files' => 'Import product tab data from an Excel file.',
            'flags' => 'Configure product flags, feature colors and sold-out display colors.',
            'pages' => 'Configure guide and FAQ front-office route bases and pagination.',
            'holiday' => 'Override holiday opening hours per store.',
            'cron' => 'Use these secure URLs to run Everblock maintenance tasks from cron.',
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['languages'] as $language) {
            $langId = (int) $language['id_lang'];
            $label = (string) ($language['iso_code'] ?? $langId);
            $builder
                ->add('EVEROPTIONS_TITLE_' . $langId, TextType::class, [
                    'label' => 'New order step title (' . $label . ')',
                    'required' => false,
                    'help' => 'If not set, new order step will not be shown.',
                ])
                ->add('EVER_TAB_TITLE_' . $langId, TextareaType::class, [
                    'label' => 'Title for global catalog tab (' . $label . ')',
                    'required' => false,
                    'attr' => ['rows' => 2],
                    'help' => 'Leaving empty will hide tab.',
                ])
                ->add('EVER_TAB_CONTENT_' . $langId, TextareaType::class, [
                    'label' => 'Text shown on global catalog tab (' . $label . ')',
                    'required' => false,
                    'attr' => ['rows' => 8, 'class' => 'autoload_rte'],
                    'help' => 'Leaving empty will hide tab.',
                ]);
        }

        $builder
            ->add('EVEROPTIONS_POSITION', ChoiceType::class, [
                'label' => 'New order step position',
                'choices' => [
                    'After login' => 1,
                    'After address form' => 2,
                    'After shipping form' => 3,
                ],
                'required' => false,
            ])
            ->add('EVERBLOCK_MAINTENANCE_PSSWD', TextType::class, [
                'label' => 'Maintenance password',
                'required' => false,
                'help' => 'People with the password will be able to access the store in maintenance mode.',
            ]);

        $this->addSwitch($builder, 'EVERPSCSS_CACHE', 'Empty cache on saving ?');
        $this->addSwitch($builder, 'EVERBLOCK_CACHE', 'Use module cache system instead of Prestashop native cache ?');
        $this->addSwitch($builder, 'EVERBLOCK_LOAD_FRONT_CSS', 'Load everblock.css on the front office ?');
        $this->addSwitch($builder, 'EVERBLOCK_USE_OBF', 'Enable front-office script for obfuscation ?');
        $this->addSwitch($builder, 'EVERBLOCK_TINYMCE', 'Extends TinyMCE on blocks management ?');
        $this->addSwitch($builder, 'EVERBLOCK_DISABLE_WEBP', 'Disable automatic conversion of images to webp format');

        $builder
            ->add('EVERPS_DUMMY_NBR', TextType::class, [
                'label' => 'Number of fictitious products to create during product generation',
                'required' => false,
            ])
            ->add('EVERPSCSS_P_LLOREM_NUMBER', TextType::class, [
                'label' => 'Default number of paragraphs when [llorem] shortcode is detected',
                'required' => false,
            ])
            ->add('EVERPSCSS_S_LLOREM_NUMBER', TextType::class, [
                'label' => 'Default number of sentences per paragraphs when [llorem] shortcode is detected',
                'required' => false,
            ])
            ->add('EVERPS_TAB_NB', TextType::class, [
                'label' => 'Number of tabs for the product page',
                'required' => false,
            ])
            ->add('EVERINSTA_ACCESS_TOKEN', TextType::class, [
                'label' => 'Instagram access token',
                'required' => false,
            ]);

        if ($options['has_instagram_token']) {
            $builder
                ->add('EVERINSTA_LINK', TextType::class, [
                    'label' => 'Instagram profile link',
                    'required' => false,
                ]);
            $this->addSwitch($builder, 'EVERINSTA_SHOW_CAPTION', 'Display Instagram post text');
        }

        $builder
            ->add('EVERWP_API_URL', TextType::class, [
                'label' => 'WordPress API URL',
                'required' => false,
                'help' => 'Example: https://example.com/wp-json/wp/v2/posts',
            ])
            ->add('EVERWP_BLOG_URL', TextType::class, [
                'label' => 'Blog URL',
                'required' => false,
                'help' => 'Use an absolute URL or a relative path such as /blog.',
            ])
            ->add('EVERWP_POST_NBR', TextType::class, [
                'label' => 'Number of blog posts to display',
                'required' => false,
            ])
            ->add('EVERWP_POSTS_BG_IMAGE', FileType::class, [
                'label' => 'Background image for WordPress posts',
                'required' => false,
                'mapped' => false,
                'help' => 'Optional background image for the latest WordPress posts section.',
            ])
            ->add('EVERBLOCK_GOOGLE_API_KEY', TextType::class, [
                'label' => 'Google Places API key',
                'required' => false,
            ])
            ->add('EVERBLOCK_GOOGLE_PLACE_ID', TextType::class, [
                'label' => 'Google Place ID',
                'required' => false,
            ])
            ->add('EVERBLOCK_GOOGLE_REVIEWS_LIMIT', TextType::class, [
                'label' => 'Maximum number of reviews',
                'required' => false,
            ])
            ->add('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING', TextType::class, [
                'label' => 'Minimum rating to display',
                'required' => false,
            ])
            ->add('EVERBLOCK_GOOGLE_REVIEWS_SORT', ChoiceType::class, [
                'label' => 'Reviews sort order',
                'choices' => [
                    'Most relevant' => 'most_relevant',
                    'Most recent' => 'newest',
                ],
                'required' => false,
            ]);

        $this->addSwitch($builder, 'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING', 'Show overall rating');
        $this->addSwitch($builder, 'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR', 'Show reviewer photos');
        $this->addSwitch($builder, 'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA', 'Show call-to-action button');

        $builder
            ->add('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL', TextType::class, [
                'label' => 'CTA label',
                'required' => false,
            ])
            ->add('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL', TextType::class, [
                'label' => 'CTA link override',
                'required' => false,
                'help' => 'Leave empty to use the Google listing URL.',
            ])
            ->add('EVERBLOCK_GMAP_KEY', TextType::class, [
                'label' => 'Google Map API key (CMS page only)',
                'required' => false,
            ])
            ->add('EVERBLOCK_MARKER_ICON', FileType::class, [
                'label' => 'Store locator marker icon',
                'required' => false,
                'mapped' => false,
                'help' => 'Only SVG files are allowed.',
            ]);

        $this->addSwitch($builder, 'EVERBLOCK_STORELOCATOR_TOGGLE', 'Display map toggle button');
        $this->addSwitch($builder, 'EVERBLOCK_SOLDOUT_FLAG', 'Show Sold out flag');

        $builder
            ->add('EVERPS_FEATURES_AS_FLAGS', ChoiceType::class, [
                'label' => 'Features as flags',
                'choices' => $options['feature_choices'],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search features',
                ],
                'help' => 'The selected features will be converted into product flags.',
            ])
            ->add('EVERPS_FLAG_NB', TextType::class, [
                'label' => 'Number of flags for products',
                'required' => false,
            ])
            ->add('EVER_SOLDOUT_COLOR', TextType::class, [
                'label' => 'Background color for Sold out flag',
                'required' => false,
                'attr' => ['type' => 'color'],
            ])
            ->add('EVER_SOLDOUT_TEXTCOLOR', TextType::class, [
                'label' => 'Text color for Sold out flag',
                'required' => false,
                'attr' => ['type' => 'color'],
            ]);

        foreach ($options['banned_features'] as $featureId) {
            $featureId = (int) $featureId;
            $featureName = $options['feature_names'][$featureId] ?? ('#' . $featureId);
            $builder
                ->add('EVERPS_FEATURE_COLOR_' . $featureId, TextType::class, [
                    'label' => 'Background color for Feature: ' . $featureName,
                    'required' => false,
                    'attr' => ['type' => 'color'],
                ])
                ->add('EVERPS_FEATURE_TEXTCOLOR_' . $featureId, TextType::class, [
                    'label' => 'Text color for Feature: ' . $featureName,
                    'required' => false,
                    'attr' => ['type' => 'color'],
                ]);
        }

        $builder
            ->add('EVERPSCSS', TextareaType::class, [
                'label' => 'Code CSS personnalisé',
                'required' => false,
                'attr' => ['rows' => 10, 'class' => 'everblock-code'],
            ])
            ->add('EVERPSJS', TextareaType::class, [
                'label' => 'Javascript / jQuery personnalisé',
                'required' => false,
                'attr' => ['rows' => 10, 'class' => 'everblock-code'],
            ])
            ->add('EVERPSCSS_LINKS', TextareaType::class, [
                'label' => 'Liens CSS personnalisés',
                'required' => false,
                'attr' => ['rows' => 5],
                'help' => 'Add one link per line, must be CSS.',
            ])
            ->add('EVERPSJS_LINKS', TextareaType::class, [
                'label' => 'Liens javascript personnalisés',
                'required' => false,
                'attr' => ['rows' => 5],
                'help' => 'Add one link per line, must be JS.',
            ])
            ->add('EVERPS_HEADER_SCRIPTS', TextareaType::class, [
                'label' => 'Header scripts',
                'required' => false,
                'attr' => ['rows' => 7],
            ])
            ->add('TABS_FILE', FileType::class, [
                'label' => 'Upload Excel tabs file',
                'required' => false,
                'mapped' => false,
            ])
            ->add('EVERBLOCK_PAGES_BASE_URL', TextType::class, [
                'label' => 'Pages base URL',
                'required' => false,
                'help' => 'Leave empty to keep the default "guide" value.',
            ])
            ->add('EVERBLOCK_PAGES_PER_PAGE', TextType::class, [
                'label' => 'Items per page',
                'required' => false,
            ])
            ->add('EVERBLOCK_FAQ_BASE_URL', TextType::class, [
                'label' => 'FAQ base URL',
                'required' => false,
                'help' => 'Leave empty to keep the default "faq" value.',
            ])
            ->add('EVERBLOCK_FAQ_PER_PAGE', TextType::class, [
                'label' => 'FAQ per page',
                'required' => false,
            ])
            ->add('EVERPS_OLD_URL', TextType::class, [
                'label' => 'Migration : Old URL',
                'required' => false,
            ])
            ->add('EVERPS_NEW_URL', TextType::class, [
                'label' => 'Migration : New URL',
                'required' => false,
            ]);

        foreach ($options['stores'] as $store) {
            foreach ($options['holidays'] as $date) {
                $builder->add('EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date, TextType::class, [
                    'label' => sprintf('Holiday hours for %s on %s', $store['name'], $date),
                    'required' => false,
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'banned_features' => [],
            'csrf_protection' => true,
            'feature_choices' => [],
            'feature_names' => [],
            'has_instagram_token' => false,
            'holidays' => [],
            'languages' => [],
            'stores' => [],
            'translation_domain' => 'Modules.Everblock.Admin',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    private function addSwitch(FormBuilderInterface $builder, string $name, string $label): void
    {
        $builder->add($name, ChoiceType::class, [
            'label' => $label,
            'choices' => [
                'Enabled' => 1,
                'Disabled' => 0,
            ],
            'expanded' => true,
            'multiple' => false,
            'required' => true,
        ]);
    }
}
