<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\CalendarRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation', name: 'reservation_')]
class ReservationController extends AbstractController
{
    #[Route('/ajax-check', name: 'new_reservation_check')]
    public function new_reservation_check(
        Request $request,
        ReservationRepository $reservationRepository,
        $update = false,
        $reservation = null
    ): JsonResponse {

        $validation = true;

        $room = $request->query->get('room');
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $reservations = $reservationRepository->get_date_conflicts($room, $start, $end, $update, $reservation);

        if (count($reservations) > 0) {
            $validation = false;
        }

        return new JsonResponse(
            ['validation' => $validation]
        );
    }

    #[Route('/{slug}', name: 'app_public_reservation')]
    public function public_reservation(
        UserRepository $userRepository,
        CalendarRepository $calendarRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        string $slug
    ): Response {
        $calendar = $calendarRepository->findOneBy(['slug' => $slug]);
        $reservation = new Reservation;
        $form = $this->createForm(ReservationFormType::class, $reservation, ['rooms' => $calendar->getRoom(), 'user' => $this->getUser()])
            ->add('reserver', SubmitType::class, [
                'label' => 'Réserver',
                'attr' => ['class' => 'btn btn-primary col-12 mt-3']
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser() != null ? $userRepository->find($this->getUser()) : null;
            if ($user && $user == $calendar->getUser()) {
                $reservation->setApproved(true);
            } else {
                $reservation->setApproved(false);
            }

            try {
                $entityManagerInterface->persist($reservation);
                $entityManagerInterface->flush();
                $this->addFlash('success', 'Votre réservation a été enregistrée.');
            } catch (\Throwable $th) {
                $this->addFlash('error', "La réservation n'a pas pu être enregistrée, veuillez réessayer.");
            }            

            return $this->redirectToRoute('app_public_calendar_view', ['slug' => $slug]);
        }

        return $this->render('user/reservations/new.html.twig', [
            'form' => $form,
        ]);
    }


    // modifier la réservation
    // annuler la réservation
    // service de mails
}
