<?php

declare(strict_types=1);

namespace Everblock\Tools\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlockType extends AbstractType
{
    public static function tabs(): array
    {
        return [
            'general' => 'General',
            'targeting' => 'Targeting',
            'display' => 'Display',
            'modal' => 'Modal',
            'schedule' => 'Schedule',
        ];
    }

    public static function fieldTabs(array $languages): array
    {
        $tabs = [
            'general' => ['name', 'id_hook', 'active'],
            'targeting' => [
                'only_home',
                'only_category',
                'only_category_product',
                'categories',
                'only_manufacturer',
                'manufacturers',
                'only_supplier',
                'suppliers',
                'only_cms_category',
                'cms_categories',
                'groups',
            ],
            'display' => [
                'obfuscate_link',
                'add_container',
                'lazyload',
                'device',
                'bootstrap_class',
                'position',
                'background',
                'css_class',
                'data_attribute',
            ],
            'modal' => ['modal', 'delay', 'timeout'],
            'schedule' => ['date_start', 'date_end'],
        ];

        foreach ($languages as $language) {
            $langId = (int) $language['id_lang'];
            $tabs['general'][] = 'content_' . $langId;
            $tabs['general'][] = 'custom_code_' . $langId;
        }

        return $tabs;
    }

    public static function tabHelp(): array
    {
        return [
            'general' => 'Main block identity, hook position and multilingual content.',
            'targeting' => 'Restrict display to specific pages, entities and customer groups.',
            'display' => 'Control layout, CSS classes, lazy loading and link obfuscation.',
            'modal' => 'Configure popup rendering, cookie lifetime and display delay.',
            'schedule' => 'Limit block visibility to a date range.',
        ];
    }

    public static function fieldDescriptions(array $languages): array
    {
        $descriptions = [
            'name' => 'Internal block name used only in the back office to identify this block quickly.',
            'id_hook' => 'Display hook where the block will be rendered. Only display hooks are listed.',
            'active' => 'Enable or disable this block without deleting its content or targeting rules.',
            'only_home' => 'Restrict this block to the homepage. Do not combine with category-only targeting.',
            'only_category' => 'Restrict this block to category pages. Use the category selector below to narrow the scope.',
            'only_category_product' => 'Restrict this block to product pages whose product belongs to selected categories.',
            'categories' => 'Selected categories used by category and product-category targeting rules.',
            'only_manufacturer' => 'Restrict this block to manufacturer pages.',
            'manufacturers' => 'Selected manufacturers where this block is allowed to appear.',
            'only_supplier' => 'Restrict this block to supplier pages.',
            'suppliers' => 'Selected suppliers where this block is allowed to appear.',
            'only_cms_category' => 'Restrict this block to CMS category pages.',
            'cms_categories' => 'Selected CMS categories where this block is allowed to appear.',
            'groups' => 'Customer groups allowed to see this block. Leave empty to allow every group.',
            'obfuscate_link' => 'Obfuscate links contained in the rendered HTML content.',
            'add_container' => 'Wrap the block in a Bootstrap container to align it with the theme content width.',
            'lazyload' => 'Delay loading where the front template supports lazy content rendering.',
            'device' => 'Choose whether the block is rendered for all devices or a specific device family.',
            'bootstrap_class' => 'Set the Bootstrap column width applied to the block wrapper.',
            'position' => 'Sort order for blocks attached to the same hook. Lower values are displayed first.',
            'background' => 'Optional background color applied to the block wrapper.',
            'css_class' => 'Additional CSS classes added to the block wrapper.',
            'data_attribute' => 'Optional data attributes added to the block wrapper, for example data-tracking="home".',
            'modal' => 'Render this block as a modal instead of inline content.',
            'delay' => 'Number of days before the modal can be shown again to the same visitor. Use 0 for debugging.',
            'timeout' => 'Delay in milliseconds before the modal appears after page load.',
            'date_start' => 'Optional date and time when the block starts being visible.',
            'date_end' => 'Optional date and time when the block stops being visible.',
        ];

        foreach ($languages as $language) {
            $langId = (int) $language['id_lang'];
            $label = (string) ($language['iso_code'] ?? $langId);
            $descriptions['content_' . $langId] = 'HTML content rendered for language ' . $label . '. Extended TinyMCE is available when enabled in configuration.';
            $descriptions['custom_code_' . $langId] = 'Optional custom code rendered before the block content for language ' . $label . '.';
        }

        return $descriptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'help' => 'Internal reminder, shown in back office only.',
            ])
            ->add('id_hook', ChoiceType::class, [
                'label' => 'Hook',
                'choices' => $options['hook_choices'],
                'attr' => [
                    'class' => 'everblock-enhanced-select',
                    'data-everblock-placeholder' => 'Search hooks',
                ],
            ])
            ->add('position', IntegerType::class, ['label' => 'Position', 'required' => false])
            ->add('active', CheckboxType::class, ['label' => 'Active', 'required' => false])
            ->add('only_home', CheckboxType::class, ['label' => 'Home only', 'required' => false])
            ->add('only_category', CheckboxType::class, ['label' => 'Category only', 'required' => false])
            ->add('only_category_product', CheckboxType::class, ['label' => 'Product category only', 'required' => false])
            ->add('only_manufacturer', CheckboxType::class, ['label' => 'Manufacturer only', 'required' => false])
            ->add('only_supplier', CheckboxType::class, ['label' => 'Supplier only', 'required' => false])
            ->add('only_cms_category', CheckboxType::class, ['label' => 'CMS category only', 'required' => false])
            ->add('obfuscate_link', CheckboxType::class, ['label' => 'Obfuscate links', 'required' => false])
            ->add('add_container', CheckboxType::class, ['label' => 'Add container', 'required' => false])
            ->add('lazyload', CheckboxType::class, ['label' => 'Lazy load', 'required' => false])
            ->add('modal', CheckboxType::class, ['label' => 'Render as modal', 'required' => false])
            ->add('device', ChoiceType::class, [
                'label' => 'Device',
                'choices' => ['All devices' => 0, 'Only mobile devices' => 4, 'Only tablet devices' => 2, 'Only desktop devices' => 1],
                'required' => true,
                'attr' => [
                    'class' => 'everblock-enhanced-select',
                    'data-everblock-placeholder' => 'Search device mode',
                ],
            ])
            ->add('bootstrap_class', ChoiceType::class, [
                'label' => 'Bootstrap size',
                'choices' => ['None' => 0, '100%' => 1, '1/2' => 2, '1/3' => 3, '1/4' => 4, '1/6' => 6],
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-select',
                    'data-everblock-placeholder' => 'Search display size',
                ],
            ])
            ->add('background', TextType::class, ['label' => 'Background color', 'required' => false, 'attr' => ['type' => 'color']])
            ->add('css_class', TextType::class, ['label' => 'CSS class', 'required' => false])
            ->add('data_attribute', TextType::class, ['label' => 'Data attributes', 'required' => false])
            ->add('categories', ChoiceType::class, [
                'label' => 'Limit on categories ?',
                'choices' => $options['category_choices'],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search categories',
                ],
            ])
            ->add('manufacturers', ChoiceType::class, [
                'label' => 'Limit on manufacturers ?',
                'choices' => $options['manufacturer_choices'],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search manufacturers',
                ],
            ])
            ->add('suppliers', ChoiceType::class, [
                'label' => 'Limit on suppliers ?',
                'choices' => $options['supplier_choices'],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search suppliers',
                ],
            ])
            ->add('cms_categories', ChoiceType::class, [
                'label' => 'Limit on CMS categories ?',
                'choices' => $options['cms_category_choices'],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search CMS categories',
                ],
            ])
            ->add('groups', ChoiceType::class, [
                'label' => 'Customer groups',
                'choices' => $options['group_choices'],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search groups',
                ],
            ])
            ->add('delay', IntegerType::class, ['label' => 'Cookie lifetime in days', 'required' => false])
            ->add('timeout', IntegerType::class, ['label' => 'Modal delay in milliseconds', 'required' => false])
            ->add('date_start', TextType::class, [
                'label' => 'Start date',
                'required' => false,
                'attr' => [
                    'class' => 'everblock-datetime-field',
                    'autocomplete' => 'off',
                    'data-everblock-datetime' => '1',
                    'placeholder' => 'YYYY-MM-DD HH:MM:SS',
                ],
            ])
            ->add('date_end', TextType::class, [
                'label' => 'End date',
                'required' => false,
                'attr' => [
                    'class' => 'everblock-datetime-field',
                    'autocomplete' => 'off',
                    'data-everblock-datetime' => '1',
                    'placeholder' => 'YYYY-MM-DD HH:MM:SS',
                ],
            ]);

        foreach ($options['languages'] as $language) {
            $langId = (int) $language['id_lang'];
            $label = (string) ($language['iso_code'] ?? $langId);
            $builder
                ->add('content_' . $langId, TextareaType::class, [
                    'label' => 'Content (' . $label . ')',
                    'required' => false,
                    'attr' => ['rows' => 12, 'class' => 'autoload_rte evertranslatable'],
                ])
                ->add('custom_code_' . $langId, TextareaType::class, [
                    'label' => 'Custom code (' . $label . ')',
                    'required' => false,
                    'attr' => ['rows' => 6],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'languages' => [],
            'hook_choices' => [],
            'category_choices' => [],
            'cms_category_choices' => [],
            'csrf_protection' => true,
            'group_choices' => [],
            'manufacturer_choices' => [],
            'supplier_choices' => [],
        ]);
    }
}
