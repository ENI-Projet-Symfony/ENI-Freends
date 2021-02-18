<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Form\GroupeType;
use App\Repository\GroupeRepository;
use claviska\SimpleImage;
use Doctrine\ORM\EntityManagerInterface;
use PHPTokenGenerator\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupeController extends AbstractController
{
    /**
     * @Route("/groupe/creergroupe", name="groupe_add")
     */
    public function creerGroupe(EntityManagerInterface $entityManager, Request $request): Response
    {
        $groupe = new Groupe();
        $groupeform = $this->createForm(GroupeType::class,$groupe);

        $groupeform->handleRequest($request);

        if($groupeform->isSubmitted() && $groupeform->isValid())
        {
            $proprio = $this->getUser();
            $groupe->setProprietaire($proprio);
            $groupe->addMembre($proprio);

            $this->addFlash('success','La création du groupe a été effectué');
            $entityManager->persist($groupe);
            $entityManager->flush();
        }

        return $this->render('groupe/creergroupe.html.twig', [
            'groupe_form' => $groupeform->createView(),
        ]);
    }

    /**
     * @Route("/groupe/detail/{id}", name="groupe_detail")
     */
    public function detailGroupe(int $id, GroupeRepository $groupeRepository): Response
    {
        $groupe = $groupeRepository->find($id);
        return $this->render('groupe/detailGroupe.html.twig', [
            'groupe' => $groupe,
            ]
        );
    }
}
