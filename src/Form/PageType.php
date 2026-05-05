<?php

declare(strict_types=1);

namespace Everblock\Tools\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('active', CheckboxType::class, ['label' => 'Active', 'required' => false])
            ->add('position', IntegerType::class, ['label' => 'Position', 'required' => false])
            ->add('group_ids', ChoiceType::class, [
                'label' => 'Allowed customer groups',
                'choices' => $options['group_choices'],
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'everblock-enhanced-multiselect',
                    'data-everblock-placeholder' => 'Search groups',
                ],
            ])
            ->add('cover_image', FileType::class, [
                'label' => 'Featured image',
                'mapped' => false,
                'required' => false,
            ]);

        foreach ($options['languages'] as $language) {
            $langId = (int) $language['id_lang'];
            $label = (string) ($language['iso_code'] ?? $langId);
            $builder
                ->add('name_' . $langId, TextType::class, ['label' => 'Page name (' . $label . ')'])
                ->add('title_' . $langId, TextType::class, ['label' => 'Meta title (' . $label . ')'])
                ->add('meta_description_' . $langId, TextareaType::class, ['label' => 'Meta description (' . $label . ')', 'required' => false, 'attr' => ['rows' => 2]])
                ->add('short_description_' . $langId, TextareaType::class, ['label' => 'Short description (' . $label . ')', 'required' => false, 'attr' => ['rows' => 4]])
                ->add('link_rewrite_' . $langId, TextType::class, ['label' => 'Friendly URL (' . $label . ')', 'required' => false])
                ->add('content_' . $langId, TextareaType::class, ['label' => 'Content (' . $label . ')', 'required' => false, 'attr' => ['rows' => 12, 'class' => 'autoload_rte evertranslatable']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'languages' => [],
            'group_choices' => [],
        ]);
    }
}
