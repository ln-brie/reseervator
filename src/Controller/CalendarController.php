<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Room;
use App\Repository\CalendarRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


#[Route('/calendar')]
class CalendarController extends AbstractController
{
    #[Route('/', name: 'app_calendar')]
    public function index(): Response
    {
        return $this->render('calendar/index.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }

    #[Route('/{base_user}/{slug}', name: 'app_public_calendar_view')]
    public function public_calendar_view(
        CalendarRepository $calendarRepository,
        string $base_user, // id user
        string $slug // slug calendrier
    ): Response
    {
        $calendarOwner = base64_decode($base_user);
        $calendar = $calendarRepository->findOneBy(['user' => $calendarOwner, 'slug' => $slug]);
        $reservations = [];
        $rooms = [];
        foreach ($calendar->getRoom() as $room) {
            $rooms[] = $room;
            foreach ($room->getReservations() as $reservation) {
                $reservations[] = array(
                    'id' => $reservation->getId(),
                    'title' => $reservation->getName(),
                    'start' => date_format($reservation->getStartsAt(), 'Y-m-d H:i'),
                    'end' => date_format($reservation->getEndsAt(), 'Y-m-d H:i'),
                    'backgroundColor' => $room->getColor(),
                    'comment' => $reservation->getComment(),
                    'room' => $room->getName()
                );
            }
        }

        return $this->render('calendar/view.html.twig', [
            'reservations' => json_encode($reservations),
            'calendar' => $calendar,
            'rooms' => $rooms
        ]);
    }

    #[Route('/ajax', name: 'app_calendar_ajax')]
    public function calendar_ajax(
        RoomRepository $roomRepository,
        CalendarRepository $calendarRepository,
        Request $request
    ): JsonResponse {

        
        $room = $roomRepository->find($request->get('id'));
        $reservations = [];
        $resas = $room->getReservations();

        foreach ($resas as $reservation) {
            $reservations[] = array(
                'id' => $reservation->getId(),
                'title' => $reservation->getName(),
                'start' => date_format($reservation->getStartsAt(), 'Y-m-d H:i'),
                'end' => date_format($reservation->getEndsAt(), 'Y-m-d H:i'),
                'backgroundColor' => $room->getColor(),
                'comment' => $reservation->getComment(),
                'room' => $room->getName()
            );
        }

        return new JsonResponse(
            ['reservations' => $reservations]
        );

    }
}
