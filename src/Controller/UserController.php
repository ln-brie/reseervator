<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationFormType;
use App\Form\RoomFormType;
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
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard')]
class UserController extends AbstractController
{

    #[Route('/', name: 'app_user_dashboard')]
    public function user_dashboard(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/rooms', name: 'app_user_rooms')]
    public function user_rooms(
        RoomRepository $roomRepository
    ): Response {
        $rooms = $roomRepository->findByOwner($this->getUser(), ['createdAt' => 'DESC']);
        return $this->render('user/rooms/list.html.twig', [
            'rooms' => $rooms
        ]);
    }

    #[Route('/rooms/details/{id}', name: 'app_user_room_details')]
    public function user_room_details(
        Room $room,
        ReservationRepository $reservationRepository
    ): Response {

        $reservations = $reservationRepository->findBy(['room' => $room], ['startsAt' => 'DESC']);
        $calendars = $room->getCalendars();

        return $this->render('user/rooms/details.html.twig', [
            'room' => $room,
            'reservations' => $reservations,
            'calendars' => $calendars
        ]);
    }

    #[Route('/rooms/new', name: 'app_user_create_room')]
    public function user_create_room(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        SluggerInterface $sluggerInterface
    ): Response {


        $user = $userRepository->find($this->getUser());
        $newRoom = new Room();
        $newRoom->setOwner($this->getUser())
            ->setPublicCode('tmp_code');
        $form = $this->createForm(RoomFormType::class, $newRoom)
            ->add('valider', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $sluggerInterface->slug($newRoom->getName());

            $entityManagerInterface->persist($newRoom);
            $entityManagerInterface->flush();

            $newRoom->setPublicCode($newRoom->getId() . $user->getId() . '-' . $slug);

            $calendar = new Calendar;
            $calendar->setIsNative(true)
            ->addRoom($newRoom)
            ->setUser($user)
            ->setName($newRoom->getName());
            $entityManagerInterface->persist($calendar);

            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_user_rooms');
        }

        return $this->render('user/rooms/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/reservations', name: 'app_user_reservations')]
    public function user_reservations(ReservationRepository $reservationRepository)
    {
        $reservations = $reservationRepository->findByApplicant($this->getUser(), ['startsAt' => 'ASC']);

        return $this->render('/user/reservations/list.html.twig', [
            'reservations' => $reservations
        ]);
    }

    #[Route('/reservations/new', name: 'app_user_new_reservation')]
    public function user_new_reservation(
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface,
        Request $request
        )
    {
        $user = $userRepository->find($this->getUser());

        $reservation = new Reservation;
        $reservation->setApplicant($this->getUser())
                    ->setApplicantName($user->getAlias())
                    ->setApproved(true);

        $form = $this->createForm(ReservationFormType::class, $reservation, ['rooms' => $user->getRooms()])
        ->add('reserver', SubmitType::class, [
            'label' => 'Réserver',
            'attr' => ['class' => 'btn btn-primary col-12']
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->persist($reservation);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_user_reservations');
        }


        return $this->render('/user/reservations/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/reservations/update/{id}', name: 'app_user_update_reservation')]
    public function user_update_reservation(
        Reservation $reservation,
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface,
        Request $request
    ) {
        $user = $userRepository->find($this->getUser());

        $rooms = $user->getRooms();
        $rooms[] = $reservation->getRoom();

        $form = $this->createForm(ReservationFormType::class, $reservation, ['rooms' => $rooms])
            ->add('reserver', SubmitType::class, [
                'label' => 'Réserver',
                'attr' => ['class' => 'btn btn-primary col-12']
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_user_reservations');
        }

        return $this->render('/user/reservations/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/reservation/ajax-check', name: 'app_user_new_reservation_check')]
    public function new_reservation_check(
        Request $request,
        RoomRepository $roomRepository,
        ReservationRepository $reservationRepository
    ): JsonResponse {

        $validation = true;

        $room = $request->query->get('room');
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $reservations = $reservationRepository->get_date_conflicts($room, $start, $end);
        
        //dd($room, $start, $end);
        if(count($reservations) > 0) {
            $validation = false;
        }

        return new JsonResponse(
            ['validation' => $validation]
        );
    }

}
