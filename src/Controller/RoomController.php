<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends AbstractController
{
    #[Route('/room', name: 'app_room')]
    public function index()
    {
        return $this->render('room/details.html.twig', [
            'controller_name' => 'RoomController',
        ]);
    }
}
