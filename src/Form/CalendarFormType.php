<?php

namespace App\Form;

use App\Entity\Calendar;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rooms = $options['rooms'];
        $calendar = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du calendrier',
                'required' => true
            ]);

        if (!empty($rooms) && $calendar->isNative() == false) {
            $builder->add('room', EntityType::class, [
                'class' => Room::class,
                'choices' => $rooms,
                'multiple' => true,
                'label' => 'Salles',
                'help' => 'Maintenez la touche Ctrl pour sélectionner plusieurs salles'
            ]);
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['rooms']);
        $resolver->setDefaults([
            'data_class' => Calendar::class,
        ]);
    }
}
