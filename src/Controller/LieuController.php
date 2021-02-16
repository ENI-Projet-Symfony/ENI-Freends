<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/lieu", name="lieu")
     */
    public function gestionLieux(Request $request, VilleRepository $villeRepository, LieuRepository $lieuRepository, EntityManagerInterface $entityManager): Response
    {
        // Ajouter un lieu
        $lieu = new Lieu();

        $formAddLieu = $this->createForm(LieuType::class, $lieu);
        $formAddLieu->handleRequest($request);

        if($formAddLieu->isSubmitted() && $formAddLieu->isValid()){
            $lieu = $formAddLieu->getData();

            $ville = $formAddLieu->get('ville')->getData();

            $lieu->setVille($villeRepository->findOneBy([
                'nom' => $ville
            ])->getId());
            $entityManager->persist($lieu);
            $entityManager->flush();
        }

        return $this->render('sortie/editer_sortie.html.twig', [
            'form_add_lieu' => $formAddLieu->createView(),
            'controller_name' => 'LieuController',
        ]);
    }
}
