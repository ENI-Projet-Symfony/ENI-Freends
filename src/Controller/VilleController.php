<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleFormType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class VilleController extends AbstractController
{
    /**
     * @Route("/villes/{page}", name="villes")
     */
    public function gestionVilles(Request $request, VilleRepository $villeRepository, EntityManagerInterface $entityManager, int $page = 1): Response
    {
        $ville = New Ville();

        $formAjout = $this->createForm(VilleFormType::class, $ville);
        $formAjout->handleRequest($request);

        if($formAjout->isSubmitted() && $formAjout->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success','La ville à bien été ajoutée');
            return $this->redirectToRoute("villes");
        }
        $data = $villeRepository->getVillesEtNbrLieux($page);


        return $this->render('villes/villes.html.twig', [
            'form_ajout' => $formAjout->createView(),
            'villes'=> $data['results'],
            'totalResultsCount' => $data['totalResultsCount'],
            'numberOfResultsPerPage'=>$data['numberOfResultsPerPage'],
            'page'=> $page,
            'controller_name' => 'VilleController',
        ]);
    }
    /**
     * @Route("/villes/supprimer/{id}", name="supprimer_ville")
     */
    public function supprimerVille(int $id, EntityManagerInterface $entityManager, VilleRepository $villeRepository): Response
    {
        $connection = $entityManager->getConnection();
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS = 0");
        $entityManager->remove($villeRepository->find($id));
        $entityManager->flush();
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS = 1");
        $this->addFlash('success','La ville à bien été supprimée');
        return $this->redirectToRoute('villes');
    }

    /**
     * @Route("/villes/modifier/{id}", name="modifier_ville")
     */
    public function modifierVille(int $id, Request $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository, SerializerInterface $serializer): Response
    {

        $data =$request->get('data');
        $ville = $villeRepository->find($id);
        $ville->setNom($data['nom']);
        $ville->setCodePostal($data['codePostal']);
        $entityManager->persist($ville);
        $entityManager->flush();

        return new JsonResponse([
            "status" => "deleted"
        ], 200);
    }
}
