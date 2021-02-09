<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\SortieFiltreFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties", name="sorties_list", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository, Request $request): Response
    {
        //crée une instance du formulaire de recherche (il n'est pas associé à une entité)
        $filterForm = $this->createForm(SortieFiltreFormType::class);

        //récupère les données soumises dans la requête
        $filterForm->handleRequest($request);
        // $data = $filterForm->getData();

        //les données du form sont là (s'il a été soumis)
        if($filterForm->isSubmitted()) {
            $campus = $filterForm['campus']->getData();
            $nom = $filterForm['nom']->getData();

            $dateDebut = $filterForm['dateHeureDebut']->getData();
            $dateFin = $filterForm['dateHeureFin']->getData();
            dump($dateDebut);

            $sortiesOrganisees = $filterForm['sortiesOrganisees']->getData();
            $sortiesInscrit = $filterForm['sortiesInscrit']->getData();
            $sortiesNonInscrit = $filterForm['sortiesNonInscrit']->getData();
            $sortiesPassees = $filterForm['sortiesPassees']->getData();

            // $participantId = $this->getUser()->getId();
            $participantId = 1;

            $sorties = $sortieRepository->filtrerSorties($campus, $nom, $participantId, $dateDebut, $dateFin, $sortiesOrganisees, $sortiesInscrit, $sortiesNonInscrit, $sortiesPassees);
        } else {
            $sorties = $sortieRepository->findAll();
        }

        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
            "sorties" => $sorties,
            'searchForm' => $filterForm->createView()
        ]);
    }

    /**
     * @Route("/sorties/{id}", name="sortie_detail", methods={"GET"})
     */
    public function details(int $id, SortieRepository $sortieRepository): Response
    {
        //aller chercher dans la BDD la sortie dont l'id est dans l'URL
        $sortie = $sortieRepository->find($id);

        // Si cette sortie n'existe pas en BDD
        if (!$sortie){
            //alors on déclenche une 404
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }

        return $this->render('sortie/details.html.twig', [
            'controller_name' => 'SortieController',
            "sortie_id" => $id,
            "sortie" => $sortie
        ]);
    }

}
