<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\RoomFormType;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    ): Response
    {
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
    public function user_create_room(Request $request, EntityManagerInterface $entityManagerInterface, SluggerInterface $sluggerInterface): Response {

        $newRoom = new Room();
        $newRoom->setOwner($this->getUser())
        ->setPublicCode('tmp_code');
        $form = $this->createForm(RoomFormType::class, $newRoom)
        ->add('valider', SubmitType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $slug = $sluggerInterface->slug($newRoom->getName());

            $entityManagerInterface->persist($newRoom);
            $entityManagerInterface->flush();

            $newRoom->setPublicCode($newRoom->getId().$this->getUser()->getId(). '-' . $slug);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_user_rooms');
        }

        return $this->render('user/rooms/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/reservations', name: 'app_user_reservations')]
    public function user_reservations(ReservationRepository $reservationRepository) {
        $reservations = $reservationRepository->findByApplicant($this->getUser(), ['startsAt' => 'DESC']);
        
        return $this->render('/user/reservations/list.html.twig', [
            'reservations' => $reservations
        ]);
    }

    #[Route('/reservations/details/{id}', name: 'app_user_reservation_details')]
    public function user_reservation_details(Reservation $reservation)
    {

        return $this->render('/user/reservations/list.html.twig', [
            'reservation' => $reservation
        ]);
    }
}
