<?php

namespace App\DataFixtures;

use App\Factory\CalendarFactory;
use App\Factory\ReservationFactory;
use App\Factory\RoomFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(50);
        RoomFactory::createMany(60, function() {
            return [
                'owner' => UserFactory::random()
            ];
        });
        ReservationFactory::createMany(100, function() {
            $applicant = rand(1, 10) > 5 ? UserFactory::random() : null;
            
            return [
                'room' => RoomFactory::random(),
                'applicant' => $applicant,
            ];
        });

        CalendarFactory::createMany(
            60,
            function() {
                return [
                    'user' => UserFactory::random(),
                    'room' => RoomFactory::randomRange(1,5)
                ];
            }
        );

        $manager->flush();
    }
}
