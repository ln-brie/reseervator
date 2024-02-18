<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Form\CalendarFormType;
use App\Form\ReservationFormType;
use App\Form\RoomFormType;
use App\Repository\CalendarRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

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
        $rooms = $roomRepository->findByOwner($this->getUser(), ['name' => 'ASC']);
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
                ->setName($newRoom->getName())
                ->setSlug($sluggerInterface->slug($calendar->getName()));
            $entityManagerInterface->persist($calendar);

            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_user_rooms');
        }

        return $this->render('user/rooms/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/rooms/update/{id}', name: 'app_user_update_room')]
    public function user_update_room(
        Room $room,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
    ): Response {
        $form = $this->createForm(RoomFormType::class, $room)
            ->add('valider', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();
            $this->addFlash('success', 'La salle ' . $room->getName() . ' a été mise à jour.');
            return $this->redirectToRoute('app_user_rooms');
        }

        return $this->render('user/rooms/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/rooms/delete/{id}', name: 'app_user_delete_room')]
    public function user_delete_room(
        Room $room,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
    ): Response {
        $entityManagerInterface->remove($room);
        $entityManagerInterface->flush();

        return $this->redirectToRoute('app_user_rooms');
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
    ) {
        $user = $userRepository->find($this->getUser());

        $reservation = new Reservation;
        $reservation->setApplicant($this->getUser())
            ->setApplicantName($user->getAlias())
            ->setApproved(true);

        $form = $this->createForm(ReservationFormType::class, $reservation, ['rooms' => $user->getRooms()])
            ->add('reserver', SubmitType::class, [
                'label' => 'Réserver',
                'attr' => ['class' => 'btn btn-primary col-12 mt-3']
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
                'attr' => ['class' => 'btn btn-primary col-12 mt-3']
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_user_reservations');
        }

        return $this->render('/user/reservations/update.html.twig', [
            'form' => $form,
            'reservation' => $reservation
        ]);
    }

    #[Route('/reservation/ajax-check', name: 'app_user_new_reservation_check')]
    public function new_reservation_check(
        Request $request,
        RoomRepository $roomRepository,
        ReservationRepository $reservationRepository,
        $update = false,
        $reservation = null
    ): JsonResponse {

        $validation = true;

        $room = $request->query->get('room');
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $reservations = $reservationRepository->get_date_conflicts($room, $start, $end, $update, $reservation);

        //dd($room, $start, $end);
        if (count($reservations) > 0) {
            $validation = false;
        }

        return new JsonResponse(
            ['validation' => $validation]
        );
    }

    #[Route('/reservation/delete/{id}', name: 'app_user_reservation_delete')]
    public function reservation_delete(Reservation $reservation, EntityManagerInterface $entityManagerInterface)
    {
        $entityManagerInterface->remove($reservation);
        $entityManagerInterface->flush();

        $this->addFlash('success', $reservation->getName() . ' a bien été supprimée.');

        return $this->redirectToRoute('app_user_reservations');
    }


    #[Route('/calendars', name: 'app_user_calendars')]
    public function user_calendars(UserRepository $userRepository)
    {
        $user = $userRepository->find($this->getUser());
        $calendars = $user->getCalendars();
        $urls = [];
        foreach ($calendars as $calendar) {
            $urls[$calendar->getId()] = $this->generateUrl('app_public_calendar_view', ['base_user' => base64_encode($user->getId()), 'slug' => $calendar->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        return $this->render('/user/calendars/list.html.twig', [
            'calendars' => $calendars,
            'urls' => $urls
        ]);
    }

    #[Route('/calendar/{id}', name: 'app_calendar_view')]
    public function calendar_view(Calendar $calendar, UserRepository $userRepository)
    {
        $user = $userRepository->find($this->getUser());
        $editable = $user == $calendar->getUser();
        $reservations = [];
        $rooms = [];
        $urls[$calendar->getId()] = $this->generateUrl('app_public_calendar_view', ['base_user' => base64_encode($user->getId()), 'slug' => $calendar->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

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
            'rooms' => $rooms,
            'editable' => $editable,
            'urls' => $urls
        ]);
    }

    #[Route('/calendars/new', name: 'app_user_new_calendar')]
    public function user_new_calendars(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManagerInterface, SluggerInterface $sluggerInterface)
    {
        $calendar = new Calendar;
        $calendar->setUser($this->getUser())
            ->setSlug('tmp')
            ->setIsNative(false);

        $user = $userRepository->find($this->getUser());
        $rooms = [];
        foreach ($user->getRooms() as $room) {
            $rooms[] = $room;
        }

        $form = $this->createForm(CalendarFormType::class, $calendar, ['rooms' => $rooms])
            ->add('Valider', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary col-12 mt-3'
                ]
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendar->setSlug($sluggerInterface->slug($calendar->getName()));
            $entityManagerInterface->persist($calendar);
            $entityManagerInterface->flush();

            return $this->redirectToRoute('app_user_calendars');
        }

        return $this->render('user/calendars/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/calendar/edit/{id}', name: 'app_user_calendar_details')]
    public function user_calendar_details(Calendar $calendar, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $user = $userRepository->find($this->getUser());
        $rooms = [];
        foreach ($user->getRooms() as $room) {
            $rooms[] = $room;
        }

        $form = $this->createForm(CalendarFormType::class, $calendar, ['rooms' => $rooms])
            ->add('Valider', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary col-12 mt-3'
                ]
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();

            return $this->redirectToRoute('app_user_calendars');
        }

        return $this->render('user/calendars/new.html.twig', [
            'form' => $form,
            'calendar' => $calendar
        ]);
    }

    #[Route('/calendar/delete/{id}', name: 'app_user_calendar_delete')]
    public function user_calendar_delete(Calendar $calendar, EntityManagerInterface $entityManagerInterface, UserRepository $userRepository)
    {
        $message = "";
        $statut = "";
        $name = $calendar->getName();
        $user = $userRepository->find($this->getUser());

        if ($user == $calendar->getUser() && !$calendar->isNative()) {
            $entityManagerInterface->remove($calendar);
            $entityManagerInterface->flush();
            $message = "Le calendrier <strong>" . $name . "</strong> a bien été supprimé.";
            $statut = "success";
        } elseif ($user != $calendar->getUser()) {
            $message = "Vous n'êtes pas propriétaire du calendrier " . $name . " , vous ne pouvez donc pas le supprimer.";
            $statut = "error";
        } else {
            $message = "Vous ne pouvez pas supprimer le calendrier " . $name . ".";
            $statut = "error";
        }

        $this->addFlash($statut, $message);

        return $this->redirectToRoute('app_user_calendars');
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function user_profile()
    {
        return $this->render('/user/profile/index.html.twig');
    }

    #[Route('/delete-account-request/{user}', name: 'app_delete_account_request')]
    public function user_delete_account_request(
        User $user,
        MailerInterface $mailerInterface,
        TokenGeneratorInterface $tokenGeneratorInterface,
        Request $request
    ) {
        $token = $tokenGeneratorInterface->generateToken();
        $email = (new TemplatedEmail())
            ->from(new Address('helene.brie@proton.me', 'Reservator'))
            ->to($user->getEmail())
            ->subject('Suppression de votre compte')
            ->htmlTemplate('user/profile/delete.html.twig')
            ->context([
                'token' => $token,
            ]);

        $mailerInterface->send($email);

        $request->getSession()->set('deleteToken', $token);

        $this->addFlash('success', 'Un email contenant un lien vous a été envoyé pour valider la suppression de votre compte.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/delete-account-validation/{token}', name: 'app_delete_account_validation')]
    public function delete_account_validation(
        EntityManagerInterface $entityManagerInterface,
        ReservationRepository $reservationRepository,
        CalendarRepository $calendarRepository,
        RoomRepository $roomRepository,
        UserRepository $userRepository,
        MailerInterface $mailerInterface,
        TokenStorageInterface $tokenStorageInterface,
        Request $request,
        string $token = null
    ) {
        if (!$token) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->getSession()->get('deleteToken') && $request->getSession()->get('deleteToken') == $token) {
            $user = $userRepository->find($this->getUser());
            $userEmail = $user->getEmail();
            foreach ($user->getReservations() as $reservation) {
                $reservationRepository->remove($reservation);
            }
            foreach ($user->getRooms() as $room) {
                $roomRepository->remove($room);
            }
            foreach ($user->getCalendars() as $calendar) {
                $calendarRepository->remove($calendar);
            }
            $userRepository->remove($user);
            $entityManagerInterface->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('helene.brie@proton.me', 'Reservator'))
                ->to($userEmail)
                ->subject('Votre compte a été supprimé.')
                ->htmlTemplate('user/profile/delete_confirmation.html.twig');

            $mailerInterface->send($email);

            $request->getSession()->invalidate();
            $tokenStorageInterface->setToken(null);

            return $this->redirectToRoute('app_logout');
        } else {
            $this->addFlash('danger', "Une erreur s'est produite, veuillez réessayer.");
            return $this->redirectToRoute('app_home');
        }
    }


    #[Route('/_reset-password/{user}', name: 'app_reset_password_request')]
    public function reset_password(
        User $user,
        ResetPasswordHelperInterface $resetPasswordHelperInterface,
        MailerInterface $mailer,
        Request $request
    ): Response {

        $request->getSession()->clear();
        $resetToken = $resetPasswordHelperInterface->generateResetToken($user);

        $email = (new TemplatedEmail())
            ->from(new Address('helene.brie@proton.me', 'Reservator'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $resetToken->clearToken();

        $request->getSession()->set('ResetPasswordToken', $resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
