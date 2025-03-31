<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $builder->add('digit_' . $i, TelType::class, [
                'label' => false,
                'attr' => [
                    'maxlength' => 1, // Limiter à un chiffre
                    'class' => 'w-full px-2 py-1 text-center border rounded-md focus:ring-blue-500 focus:border-blue-500',
                    'pattern' => '[0-9]', // Validation HTML pour un chiffre uniquement
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez remplir ce champ.']),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]$/',
                        'message' => 'Seul un chiffre est autorisé.',
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
