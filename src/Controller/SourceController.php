<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class SourceController extends AbstractController
{
    #[Route('/source', name: 'app_source')]
    public function index(): Response
    {
        dd('hhhh');
        return $this->render('source/index.html.twig', [
            'controller_name' => 'SourceController',
        ]);
    }
}
