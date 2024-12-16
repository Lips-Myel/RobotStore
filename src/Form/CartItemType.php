<?php

namespace App\Form;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Robot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            ->add('cart', EntityType::class, [
                'class' => Cart::class,
                'choice_label' => 'id',
            ])
            ->add('robot', EntityType::class, [
                'class' => Robot::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CartItem::class,
        ]);
    }
}
