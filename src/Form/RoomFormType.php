<?php

namespace App\Form;

use App\Entity\Room;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true
            ])
            ->add('color', ColorType::class, [
                'label' => 'Couleur'
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false
            ])
            ->add('registeredReservationsOnly', ChoiceType::class, [
                'label' => 'Réservable uniquement par les utilisateurs enregistrés',
                'choices' => array(
                    'Oui' => true,
                    'Non' => false
                ),
                'expanded'  => true,
                'multiple'  => false,
                'data' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
