<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UserInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function modificationProfil(EntityManagerInterface $entityManager, Request $request,UserPasswordEncoderInterface $encoder): Response
    {
        //Recupere l'utilisateur connecter
        $participant = $this->getUser();

        $userform = $this->createForm(UserInformationType::class,$participant);

        $userform->handleRequest($request);

        dump($participant);

        if($userform->isSubmitted() && $userform->isValid())
        {
            $participant->setPassword($encoder->encodePassword($participant, $userform->get('password')->getData()));

            $entityManager->persist($participant);
            $entityManager->flush();
        }

        return $this->render('participant/modificationProfil.html.twig', [
            'user_form' => $userform->createView(),
        ]);
    }
}
