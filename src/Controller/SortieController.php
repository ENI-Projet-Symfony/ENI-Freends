<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieFiltreFormType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Util\GestionDesEtats;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties/nouveau", name="sorties_add")
     */
    public function nouveaux(Request $request,EntityManagerInterface $entityManager,
                             ParticipantRepository $participantRepository,
                             EtatRepository $etatRepository ): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class,$sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            if($request->get("submit_type")==="Publier"){
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "2"
                ]));
                $this->addFlash("success","félicitation votre Sortie a bien été publier");

            }else if ($request->get("submit_type")==="Créer") {
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "1"
                ]));
                $this->addFlash("success","félicitation votre Sortie a bien été créé");
            }
            $sortie->addParticipant($this->getUser());

            $sortie->setOrganisateur($this->getUser());
            $sortie->setCampus($this->getUser()->getCampus());
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('sorties/nouveau.html.twig', [
            "formulaire" => $form->createView()
        ]);
    }

    /**
     * @Route("/sorties/modifier/{id}", name="sorties_update")
     */
    public function modifier(Request $request,EntityManagerInterface $entityManager,
                             ParticipantRepository $participantRepository,
                             EtatRepository $etatRepository,SortieRepository $sortieRepository,
                             int $id ): Response
    {
        if ($id){
            $sortie = $sortieRepository->findOneBy(['id'=>$id]);
        }else{
            throw $this->createNotFoundException('Sortie Inconnue.');
        }

        if(!$sortie){
            throw $this->createNotFoundException('Sortie Inconnue.');
        }

        $form = $this->createForm(SortieType::class,$sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            if($request->get("submit_type")==="Publier"){
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "2"
                ]));
            }else if ($request->get("submit_type")==="Créer") {
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "1"
                ]));
            }
            $sortie->addParticipant($this->getUser());

            $sortie->setOrganisateur($this->getUser());

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash("success","La sortie a été modifier");
        }

        return $this->render('sorties/update.html.twig', [
            "formulaire" => $form->createView(),
            "sortie" => $sortie
        ]);
    }

    /**
     * @Route("/sorties", name="sorties_list", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository, Request $request, GestionDesEtats $gestionDesEtats): Response
    {
        //crée une instance du formulaire de recherche (il n'est pas associé à une entité)
        $filterForm = $this->createForm(SortieFiltreFormType::class);
        //récupère les données soumises dans la requête
        $filterForm->handleRequest($request);

        $gestionDesEtats->verificationEtats();

        //les données du form sont là (s'il a été soumis)
        if($filterForm->isSubmitted()) {
            $campus = $filterForm['campus']->getData();
            $nom = $filterForm['nom']->getData();

            $dateDebut = $filterForm['dateHeureDebut']->getData();
            $dateFin = $filterForm['dateHeureFin']->getData();

            $sortiesOrganisees = $filterForm['sortiesOrganisees']->getData();
            $sortiesInscrit = $filterForm['sortiesInscrit']->getData();
            $sortiesNonInscrit = $filterForm['sortiesNonInscrit']->getData();
            $sortiesPassees = $filterForm['sortiesPassees']->getData();

            $participantId = $this->getUser()->getId();

            $sorties = $sortieRepository->filtrerSorties($campus, $nom, $participantId, $dateDebut, $dateFin, $sortiesOrganisees, $sortiesInscrit, $sortiesNonInscrit, $sortiesPassees);
        } else {
            $sorties = $sortieRepository->filtrerSortieParEtat([7]);
        }
        dump($sorties);

        return $this->render('sorties/list.html.twig', [
            'controller_name' => 'SortieController',
            "sorties" => $sorties,
            'searchForm' => $filterForm->createView()
        ]);
    }

    /**
     * @Route("/sorties/{id}", name="sorties_detail", methods={"GET"})
     */
    public function details(int $id, SortieRepository $sortieRepository, GestionDesEtats $gestionDesEtats): Response
    {
        $gestionDesEtats->verificationEtats();
        //aller chercher dans la BDD la sortie dont l'id est dans l'URL
        $sortie = $sortieRepository->find($id);

        // Si cette sortie n'existe pas en BDD
        if (!$sortie){
            //alors on déclenche une 404
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }

        return $this->render('sorties/details.html.twig', [
            'controller_name' => 'SortieController',
            "sorties_id" => $id,
            "sortie" => $sortie
        ]);
    }

    /**
     * @Route("/sorties/{id}/insciption", name="sorties_inscription", methods={"GET"})
     */
    public function inscription(int $id, EntityManagerInterface $entityManager,
                                SortieRepository $sortieRepository,
                                ParticipantRepository $participantRepository): Response
    {
        // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
        $participant = $this->getUser()->getId();
        $sortie = $sortieRepository->find($id);

        // Si cette sortie n'existe pas en BDD
        if (!$sortie){
            //alors on déclenche une 404
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }

        // Ajoute le participant à la sortie
        $sortie->addParticipant($participantRepository->findOneBy([
            'id' => $participant
        ]));

        // Enjoute l'ID du participat et l'ID de sortie dans la table "participant_sortie" de la BDD
        $entityManager->persist($sortie);
        $entityManager->flush();

        // Ajoute un message de succès
        $this->addFlash("success","Vous avez été inscit.e à cette sortie avec succès");

        // Redirige sur la page de list de Sorties
        return $this->redirectToRoute('sorties_list');
    }

    /**
     * @Route("/sorties/{id}/desinsciption", name="sorties_desinscription", methods={"GET"})
     */
    public function desinscription(int $id, EntityManagerInterface $entityManager,
                                   SortieRepository $sortieRepository,
                                   ParticipantRepository $participantRepository): Response
    {
        // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
        $participant = $this->getUser()->getId();
        $sortie = $sortieRepository->find($id);

        // Si cette sortie n'existe pas en BDD
        if (!$sortie){
            //alors on déclenche une 404
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }

        // Ajoute le participant à la sortie
        $sortie->removeParticipant($participantRepository->findOneBy([
            'id' => $participant
        ]));

        // Enjoute l'ID du participat et l'ID de sortie dans la table "participant_sortie" de la BDD
        $entityManager->persist($sortie);
        $entityManager->flush();

        // Ajoute un message de succès
        $this->addFlash("warning","Vous avez été désinscit.e de cette sortie avec succès");

        // Redirige sur la page de list de Sorties
        return $this->redirectToRoute('sorties_list');
    }

    /**
     * @Route("/sorties/{id}/publication", name="sorties_publish", methods={"GET"})
     */
    public function publishSortie(int $id, EntityManagerInterface $entityManager,
                                   SortieRepository $sortieRepository,
                                   EtatRepository $etatRepository): Response
    {

        $sortie = $sortieRepository->findOneBy(['id'=>$id]);

        $sortie->setEtat($etatRepository->findOneBy(["id"=>2]));

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash("sucess","Sortie à bien été publiée");

        return $this->redirectToRoute('sorties_list');

    }

}