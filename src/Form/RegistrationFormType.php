<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                // 'constraints' => [
                //     new Assert\NotBlank([
                //         'message' => 'L\'email est obligatoire.',
                //     ]),
                //     new Assert\Email([
                //         'message' => 'Veuillez entrer un email valide.',
                //     ]),
                // ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                // 'constraints' => [
                //     new Assert\NotBlank([
                //         'message' => 'Le mot de passe est obligatoire.',
                //     ]),
                //     new Assert\Length([
                //         'min' => 6,
                //         'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                //         'max' => 4096,
                //     ]),
                // ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions',
                'mapped' => false,
                // 'constraints' => [
                //     new Assert\IsTrue([
                //         'message' => 'Vous devez accepter les termes et conditions.',
                //     ]),
                // ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
