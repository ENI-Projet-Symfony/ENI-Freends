<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/participant/monprofil", name="participant_modification_profil")
     */
    public function modificationProfil(): Response
    {
        return $this->render('participant/modificationProfil.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
}
