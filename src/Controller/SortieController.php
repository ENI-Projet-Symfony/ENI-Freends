<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/nouveau", name="sortie_add")
     */
    public function nouveaux(Request $request,EntityManagerInterface $entityManager,
                             ParticipantRepository $participantRepository,
                             EtatRepository $etatRepository ): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class,$sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            dump($request->get("submit_type"));
            if($request->get("submit_type")==="1"){
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "2"
                ]));
            }else if ($request->get("submit_type")==="0") {
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "1"
                ]));
            }
            $sortie->addParticipant($participantRepository->findOneBy([
                'id' => "1"
            ]));

            $sortie->setOrganisateur($participantRepository->findOneBy([
                'id' => "1"
            ]));

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash("success","");
        }

        return $this->render('sortie/nouveau.html.twig', [
            "formulaire" => $form->createView()
        ]);
    }

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