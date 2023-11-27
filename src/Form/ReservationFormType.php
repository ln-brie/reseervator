<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Room;
use DateTime;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DataTransformerChain;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rooms = $options['rooms'];

        $builder
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y/m/d H:i:s'),
                    'max' => (new \DateTime())->modify('+2 years')->format('Y/m/d H:i:s')
                ]
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y/m/d H:i:s'),
                    'max' => (new \DateTime())->modify('+2 years')->format('Y/m/d H:i:s')
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false
            ])
            ->add('name', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choices' => $rooms,
                'label' => 'Salle'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['rooms']);
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
