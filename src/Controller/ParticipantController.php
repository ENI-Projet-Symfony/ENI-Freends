<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UserInformationType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantController extends AbstractController
{

    /**
     * @Route("/participant/profilUtilisateur/{id}", name="participant_profil")
     */
    public function profilUtilisateur($id, ParticipantRepository $participantRepository): Response
    {
        //Recupere l'utilisateur connecter
        $participant = $participantRepository->find($id);

        return $this->render('participant/profilUtilisateur.html.twig', [
            'id_participant' => $id,
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

        if($userform->isSubmitted() && $userform->isValid())
        {
            if($userform->get('password')->getData())
            {
                $participant->setPassword($encoder->encodePassword($participant, $userform->get('password')->getData()));
            }

            $this->addFlash('success','Les modfications de vôtre profil ont bien été effectuées');
            $entityManager->persist($participant);
            $entityManager->flush();
        }

        return $this->render('participant/modificationProfil.html.twig', [
            'user_form' => $userform->createView(),
        ]);
    }
}
