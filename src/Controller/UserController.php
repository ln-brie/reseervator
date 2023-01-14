<?php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class UserController extends AbstractController
{

    #[Route('/', name: 'app_user_dashboard')]
    public function user_dashboard()
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/rooms', name: 'app_user_rooms')]
    public function user_rooms(
        RoomRepository $roomRepository
    )
    {
        $rooms = $roomRepository->findByOwner($this->getUser());
        return $this->render('user/rooms/list.html.twig', [
            'rooms' => $rooms
        ]);
    }

    #[Route('/rooms/{id}', name: 'app_user_room_details')]
    public function user_room_details(
        Room $room
    ) {
        $reservations = $room->getReservations();
        $calendars = $room->getCalendars();
        
        return $this->render('user/rooms/details.html.twig', [
            'room' => $room,
            'reservations' => $reservations,
            'calendars' => $calendars
        ]);
    }
   
}
