<?php

namespace App\Controller;

use App\Entity\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/view/{id}', name: 'app_calendar_view')]
    public function calendar_view(
        Calendar $calendar

    ): Response
    {
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
}
