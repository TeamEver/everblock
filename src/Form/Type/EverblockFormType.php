<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Everblock\Tools\Form\Type;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_everblock', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => $this->trans('Name'),
                'required' => true,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('content', TranslateType::class, [
                'label' => $this->trans('HTML block content'),
                'locales' => $options['languages'],
                'type' => TextareaType::class,
                'options' => [
                    'attr' => [
                        'class' => 'evertranslatable',
                        'data-autoload-rte' => true,
                    ],
                    'required' => false,
                ],
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('id_hook', ChoiceType::class, [
                'label' => $this->trans('Hook'),
                'choices' => $this->formatChoices($options['hooks']),
                'required' => true,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('custom_code', TranslateType::class, [
                'label' => $this->trans('Custom code'),
                'locales' => $options['languages'],
                'type' => TextareaType::class,
                'options' => [
                    'required' => false,
                    'attr' => ['class' => 'evertranslatable-code'],
                ],
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('active', SwitchType::class, [
                'label' => $this->trans('Active'),
                'required' => false,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('groupBox', ChoiceType::class, [
                'label' => $this->trans('Customer groups'),
                'choices' => $this->formatChoices($options['groups'], 'id_group'),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('categories', ChoiceType::class, [
                'label' => $this->trans('Categories'),
                'choices' => $this->formatChoices($options['categories']),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('manufacturers', ChoiceType::class, [
                'label' => $this->trans('Manufacturers'),
                'choices' => $this->formatChoices($options['manufacturers']),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('suppliers', ChoiceType::class, [
                'label' => $this->trans('Suppliers'),
                'choices' => $this->formatChoices($options['suppliers']),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('cms_categories', ChoiceType::class, [
                'label' => $this->trans('CMS categories'),
                'choices' => $this->formatChoices($options['cms_categories']),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_home', SwitchType::class, [
                'label' => $this->trans('Home only'),
                'required' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_category', SwitchType::class, [
                'label' => $this->trans('Category only'),
                'required' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_category_product', SwitchType::class, [
                'label' => $this->trans('Product category only'),
                'required' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_manufacturer', SwitchType::class, [
                'label' => $this->trans('Manufacturer only'),
                'required' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_supplier', SwitchType::class, [
                'label' => $this->trans('Supplier only'),
                'required' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_cms_category', SwitchType::class, [
                'label' => $this->trans('CMS category only'),
                'required' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('obfuscate_link', SwitchType::class, [
                'label' => $this->trans('Obfuscate links'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('add_container', SwitchType::class, [
                'label' => $this->trans('Wrap with container'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('lazyload', SwitchType::class, [
                'label' => $this->trans('Lazyload images'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('background', TextType::class, [
                'label' => $this->trans('Background color'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('css_class', TextType::class, [
                'label' => $this->trans('Custom CSS class'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('data_attribute', TextType::class, [
                'label' => $this->trans('Data attributes'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('bootstrap_class', ChoiceType::class, [
                'label' => $this->trans('Bootstrap width'),
                'choices' => $this->formatChoices($options['bootstrap_sizes']),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('position', IntegerType::class, [
                'label' => $this->trans('Position'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('device', ChoiceType::class, [
                'label' => $this->trans('Device restriction'),
                'choices' => $this->formatChoices($options['devices']),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('modal', SwitchType::class, [
                'label' => $this->trans('Display as modal'),
                'required' => false,
                'row_attr' => ['data-tab' => 'modal'],
            ])
            ->add('delay', IntegerType::class, [
                'label' => $this->trans('Cookie lifetime (days)'),
                'required' => false,
                'row_attr' => ['data-tab' => 'modal'],
            ])
            ->add('timeout', IntegerType::class, [
                'label' => $this->trans('Display delay (ms)'),
                'required' => false,
                'row_attr' => ['data-tab' => 'modal'],
            ])
            ->add('date_start', DateTimeType::class, [
                'label' => $this->trans('Date start'),
                'required' => false,
                'widget' => 'single_text',
                'row_attr' => ['data-tab' => 'schedule'],
            ])
            ->add('date_end', DateTimeType::class, [
                'label' => $this->trans('Date end'),
                'required' => false,
                'widget' => 'single_text',
                'row_attr' => ['data-tab' => 'schedule'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'languages' => [],
            'hooks' => [],
            'categories' => [],
            'manufacturers' => [],
            'suppliers' => [],
            'cms_categories' => [],
            'groups' => [],
            'bootstrap_sizes' => [],
            'devices' => [],
            'tabs' => [],
            'documentation' => [],
            'translation_domain' => 'Modules.Everblock.Admin',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['tabs'] = $options['tabs'];
        $view->vars['documentation'] = $options['documentation'];
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param string $idKey
     *
     * @return array<string, int|string>
     */
    private function formatChoices(array $choices, string $idKey = 'id'): array
    {
        $formatted = [];

        foreach ($choices as $choice) {
            if (!isset($choice[$idKey], $choice['name'])) {
                continue;
            }

            $formatted[$choice['name']] = $choice[$idKey];
        }

        return $formatted;
    }

    private function trans(string $message): string
    {
        return \Context::getContext()->getTranslator()->trans($message, [], 'Modules.Everblock.Admin');
    }
}
