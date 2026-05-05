<?php

declare(strict_types=1);

namespace Everblock\Tools\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShortcodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('shortcode', TextType::class, ['label' => 'Shortcode']);
        foreach ($options['languages'] as $language) {
            $langId = (int) $language['id_lang'];
            $label = (string) ($language['iso_code'] ?? $langId);
            $builder
                ->add('title_' . $langId, TextType::class, ['label' => 'Title (' . $label . ')'])
                ->add('content_' . $langId, TextareaType::class, [
                    'label' => 'Content (' . $label . ')',
                    'required' => false,
                    'attr' => ['rows' => 10, 'class' => 'autoload_rte evertranslatable'],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['languages' => []]);
    }
}
