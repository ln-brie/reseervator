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
        UserFactory::createMany(10);
        
        CalendarFactory::createMany(
            15,
            function() {
                $user = UserFactory::random();
                $room = RoomFactory::createOne(['owner' => $user]);
                return [
                    'user' => $user,
                    'room' => [$room],
                    'isNative' => true
                ];
            }
        );

        ReservationFactory::createMany(200, function() {
            $applicant = rand(1, 10) > 5 ? UserFactory::random() : null;
            
            return [
                'room' => RoomFactory::random(),
                'applicant' => $applicant,
            ];
        });

        CalendarFactory::createMany(
            15,
            function() {
                return [
                    'user' => UserFactory::random(),
                    'room' => RoomFactory::randomRange(1,5),
                ];
            }
        );
        

        $manager->flush();
    }
}
