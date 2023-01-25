<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/room')]
class RoomController extends AbstractController
{
    #[Route('/{id}/calendar', name: 'app_room_calendar')]
    public function room_calendar(): Response
    {
        return $this->render('room/details.html.twig', [
            'controller_name' => 'RoomController',
        ]);
    }
}
