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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rooms = $options['rooms'];
        $user = $options['user'];

        $builder
            ->add('applicantEmail', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'data' => $user !== null ? $user->getEmail() : '',
                'attr' => [
                    'class' => 'mb-1'
                ]
            ])
            ->add('applicantName', TextType::class, [
                'label' => 'Votre nom',
                'data' => $user !== null ? $user->getAlias() : '',
                'attr' => [
                    'class' => 'mb-1'
                ]
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y/m/d H:i:s'),
                    'max' => (new \DateTime())->modify('+2 years')->format('Y/m/d H:i:s'),
                    'class' => 'mb-1'
                ]
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y/m/d H:i:s'),
                    'max' => (new \DateTime())->modify('+2 years')->format('Y/m/d H:i:s'),
                    'class' => 'mb-1'
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'class' => 'mb-1'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'mb-1'
                ]
            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choices' => $rooms,
                'label' => 'Salle',
                'attr' => [
                    'class' => 'mb-1'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['rooms', 'user']);
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
