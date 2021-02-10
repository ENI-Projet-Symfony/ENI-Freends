<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

}
