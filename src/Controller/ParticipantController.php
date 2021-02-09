<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UserInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{

    /**
     * @Route("/participant/monnprofil", name="participant_mon_profil")
     */
    public function monProfil(): Response
    {
        //Recupere l'utilisateur connecter
        $participant = $this->getUser();

        return $this->render('participant/monProfil.html.twig', [
            'participant' => $participant,
        ]);
    }

    /**
     * @Route("/participant/modificationprofil", name="participant_modification_profil")
     */
    public function modificationProfil(EntityManagerInterface $entityManager, Request $request): Response
    {
        //Recupere l'utilisateur connecter
        $participant = $this->getUser();

        $userform = $this->createForm(UserInformationType::class,$participant);

        $userform->handleRequest($request);

        if($userform->isSubmitted() && $userform->isValid())
        {

            $entityManager->persist($participant);
            $entityManager->flush();
        }

        return $this->render('participant/modificationProfil.html.twig', [
            'user_form' => $userform->createView(),
        ]);
    }
}
