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
            if ($reservation->isApproved()) {
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

        return new JsonResponse(
            ['reservations' => $reservations]
        );
    }

    #[Route('/{slug}', name: 'app_public_calendar_view')]
    public function public_calendar_view(
        CalendarRepository $calendarRepository,
        string $slug // slug calendrier
    ): Response {
        $calendar = $calendarRepository->findOneBy(['slug' => $slug]);
        $reservations = [];
        $rooms = [];
        $reservation_button = 'true';
        if ($calendar) {
            $anonymous_reservation_forbidden = 0;
            foreach ($calendar->getRoom() as $room) {
                $rooms[] = $room;
                if ($room->isRegisteredReservationsOnly() == false) {
                    $anonymous_reservation_forbidden++;
                }
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
            if ($this->getUser() == null && $anonymous_reservation_forbidden == count($rooms)) {
                $reservation_button = false;
            }
        } else {
            $this->addFlash('error', "Le calendrier que vous souhaitez afficher n'existe pas");
            return $this->redirectToRoute('app_home');
        }

        return $this->render('calendar/view.html.twig', [
            'reservations' => json_encode($reservations),
            'calendar' => $calendar,
            'rooms' => $rooms,
            'reservation_button' => $reservation_button
        ]);
    }
}
