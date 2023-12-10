<?php

namespace App\Controller;

use App\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    #[Route('/conference', name: 'app_conference')]
    public function index(): Response
    {
        $movie = new Movie();
        $movie->setTitle('test Movie');
        $movie->setTitle('test Movie');
        $jsonData = [
            'id' => '123'
        ];

        return new JsonResponse($jsonData);
    }
}
