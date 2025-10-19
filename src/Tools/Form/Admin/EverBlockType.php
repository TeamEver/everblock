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
 */

namespace Everblock\Tools\Form\Admin;

use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EverBlockType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->trans('Name'),
                'required' => true,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('id_hook', ChoiceType::class, [
                'label' => $this->trans('Hook'),
                'choices' => $options['hooks'],
                'placeholder' => $this->trans('Select a hook'),
                'choice_translation_domain' => false,
                'required' => true,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('content', TranslatableType::class, [
                'label' => $this->trans('HTML block content'),
                'required' => true,
                'type' => TextareaType::class,
                'options' => [
                    'attr' => ['class' => 'autoload_rte'],
                ],
                'locales' => $options['languages'],
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('custom_code', TranslatableType::class, [
                'label' => $this->trans('Custom code'),
                'required' => false,
                'type' => TextareaType::class,
                'options' => [
                    'attr' => ['rows' => 5],
                ],
                'locales' => $options['languages'],
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('bootstrap_class', ChoiceType::class, [
                'label' => $this->trans('Bootstrap column'),
                'choices' => $options['bootstrap_sizes'],
                'choice_translation_domain' => false,
                'required' => false,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('background', TextType::class, [
                'label' => $this->trans('Background color'),
                'required' => false,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('css_class', TextType::class, [
                'label' => $this->trans('CSS classes'),
                'required' => false,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('data_attribute', TextType::class, [
                'label' => $this->trans('Data attributes'),
                'required' => false,
                'row_attr' => ['data-tab' => 'general'],
            ])
            ->add('only_home', SwitchType::class, [
                'label' => $this->trans('Only on homepage'),
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_category', SwitchType::class, [
                'label' => $this->trans('Only on categories'),
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_category_product', SwitchType::class, [
                'label' => $this->trans('Only on products within categories'),
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('categories', ChoiceType::class, [
                'label' => $this->trans('Limit to categories'),
                'choices' => $options['categories'],
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'choice_translation_domain' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_manufacturer', SwitchType::class, [
                'label' => $this->trans('Only on manufacturers'),
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('manufacturers', ChoiceType::class, [
                'label' => $this->trans('Limit to manufacturers'),
                'choices' => $options['manufacturers'],
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'choice_translation_domain' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_supplier', SwitchType::class, [
                'label' => $this->trans('Only on suppliers'),
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('suppliers', ChoiceType::class, [
                'label' => $this->trans('Limit to suppliers'),
                'choices' => $options['suppliers'],
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'choice_translation_domain' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('only_cms_category', SwitchType::class, [
                'label' => $this->trans('Only on CMS categories'),
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('cms_categories', ChoiceType::class, [
                'label' => $this->trans('Limit to CMS categories'),
                'choices' => $options['cms_categories'],
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'choice_translation_domain' => false,
                'row_attr' => ['data-tab' => 'targeting'],
            ])
            ->add('obfuscate_link', SwitchType::class, [
                'label' => $this->trans('Obfuscate links'),
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('add_container', SwitchType::class, [
                'label' => $this->trans('Add container'),
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('lazyload', SwitchType::class, [
                'label' => $this->trans('Lazyload images'),
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('groups', ChoiceType::class, [
                'label' => $this->trans('Customer groups'),
                'choices' => $options['groups'],
                'multiple' => true,
                'expanded' => false,
                'choice_translation_domain' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('device', ChoiceType::class, [
                'label' => $this->trans('Devices'),
                'choices' => $options['devices'],
                'choice_translation_domain' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('position', IntegerType::class, [
                'label' => $this->trans('Position'),
                'required' => false,
                'row_attr' => ['data-tab' => 'display'],
            ])
            ->add('modal', SwitchType::class, [
                'label' => $this->trans('Display as modal'),
                'row_attr' => ['data-tab' => 'modal'],
            ])
            ->add('delay', IntegerType::class, [
                'label' => $this->trans('Modal delay (ms)'),
                'required' => false,
                'row_attr' => ['data-tab' => 'modal'],
            ])
            ->add('timeout', IntegerType::class, [
                'label' => $this->trans('Modal timeout (ms)'),
                'required' => false,
                'row_attr' => ['data-tab' => 'modal'],
            ])
            ->add('date_start', TextType::class, [
                'label' => $this->trans('Start date'),
                'required' => false,
                'attr' => ['class' => 'datetimepicker'],
                'row_attr' => ['data-tab' => 'schedule'],
            ])
            ->add('date_end', TextType::class, [
                'label' => $this->trans('End date'),
                'required' => false,
                'attr' => ['class' => 'datetimepicker'],
                'row_attr' => ['data-tab' => 'schedule'],
            ])
            ->add('active', SwitchType::class, [
                'label' => $this->trans('Active'),
                'row_attr' => ['data-tab' => 'schedule'],
            ]);
    }

    private function trans(string $message, array $parameters = []): string
    {
        return $this->translator->trans($message, $parameters, 'Modules.Everblock.Admin');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'hooks',
            'categories',
            'manufacturers',
            'suppliers',
            'cms_categories',
            'groups',
            'bootstrap_sizes',
            'devices',
            'languages',
        ]);

        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
