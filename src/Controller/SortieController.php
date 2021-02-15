<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieFiltreFormType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Util\GestionDesEtats;
use claviska\SimpleImage;
use DeviceDetector\DeviceDetector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
                             EtatRepository $etatRepository, string $uploadDirImg): Response
    {
        // https://github.com/matomo-org/device-detector
        // composer require matomo/device-detector
        // Detection du support. Si mobile : redirige
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        if ($dd->isMobile())
        {
            return $this->redirectToRoute('sorties_list');
        }

        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class,$sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){

            // Récupérer l'image uploadée
            /** @var UploadedFile $picture */
            $picture = $form->get('picture')->getData();

            if($picture)
            {
                // A installer depuis https://github.com/gsylvestre/php-token-generator
                // composer require gsylvestre/php-token-generator
                // Génère un nom de fichier aléatoire avec la bonne extension
                $generator = new \PHPTokenGenerator\TokenGenerator();
                $newFilename = 'sortie_' . $generator->generate(24) . '.' . $picture->guessExtension();
                // Déplace le fichier uploadé dans public/img/
                $picture->move($uploadDirImg, $newFilename);
                // Hydrate la propriété avec le nom du fichier
                $sortie->setUrlPhoto($newFilename);

                try
                {
                    // A installer depuis https://github.com/claviska/SimpleImage/
                    // composer require claviska/simpleimage
                    // Redimensionne les images (et autres filtres)
                    $image = new SimpleImage();
                    $image->fromFile($uploadDirImg . $newFilename)
                        ->bestFit(600, 600)
                        ->toFile($uploadDirImg . "small/" . $newFilename);
                }
                catch (Exception $err)
                {
                    echo $err->getMessage();
                }
            }

            if($request->get("submit_type")==="Publier"){
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "2"
                ]));
                $this->addFlash("success","Félicitations, votre sortie a bien été publiée");

            }else if ($request->get("submit_type")==="Créer") {
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "1"
                ]));
                $this->addFlash("success","Félicitations, votre sortie a bien été créée");
            }
            $sortie->addParticipant($this->getUser());

            $sortie->setOrganisateur($this->getUser());
            $sortie->setCampus($this->getUser()->getCampus());
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('sorties/editer_sortie.html.twig', [
            "formulaire" => $form->createView()
        ]);
    }

    /**
     * @Route("/sorties/modifier/{id}", name="sorties_update")
     */
    public function modifier(Request $request,EntityManagerInterface $entityManager,
                             ParticipantRepository $participantRepository,
                             EtatRepository $etatRepository,SortieRepository $sortieRepository,
                             int $id, string $uploadDirImg): Response
    {
        if ($id){
            $sortie = $sortieRepository->findOneBy(['id'=>$id]);
        }else{
            throw $this->createNotFoundException('Sortie inexistante.');
        }

        if(!$sortie){
            throw $this->createNotFoundException('Sortie inexistante.');
        }

        $form = $this->createForm(SortieType::class,$sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            // Récupérer l'image uploadée
            /** @var UploadedFile $picture */
            $picture = $form->get('picture')->getData();

            if($picture)
            {
                // A installer depuis https://github.com/gsylvestre/php-token-generator
                // composer require gsylvestre/php-token-generator
                // Génère un nom de fichier aléatoire avec la bonne extension
                $generator = new \PHPTokenGenerator\TokenGenerator();
                $newFilename = 'sortie_' . $generator->generate(24) . '.' . $picture->guessExtension();
                // Déplace le fichier uploadé dans public/img/
                $picture->move($uploadDirImg, $newFilename);
                // Hydrate la propriété avec le nom du fichier
                $sortie->setUrlPhoto($newFilename);

                try
                {
                    // A installer depuis https://github.com/claviska/SimpleImage/
                    // composer require claviska/simpleimage
                    // Redimensionne les images (et autres filtres)
                    $image = new SimpleImage();
                    $image->fromFile($uploadDirImg . $newFilename)
                        ->bestFit(600, 600)
                        ->toFile($uploadDirImg . "small/" . $newFilename);
                }
                catch (Exception $err)
                {
                    echo $err->getMessage();
                }
            }

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

        return $this->render('sorties/editer_sortie.html.twig', [
            "formulaire" => $form->createView(),
            "sortie" => $sortie
        ]);
    }

    /**
     * @Route("/sorties", name="sorties_list", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository, Request $request, GestionDesEtats $gestionDesEtats): Response
    {
        //Crée une instance du formulaire de recherche (il n'est pas associé à une entité)
        $filterForm = $this->createForm(SortieFiltreFormType::class);
        //récupère les données soumises dans la requête
        $filterForm->handleRequest($request);

        //les données du form sont là (s'il a été soumis)
        if($filterForm->isSubmitted()) {
            $campus = $filterForm['campus']->getData();
            $nom = $filterForm['nom']->getData();

            $dateDebut = null;
            if ($filterForm['dateHeureDebut']->getData()) {
                $dateDebut = $filterForm['dateHeureDebut']->getData();
            }
            $dateFin = null;
            if ($filterForm['dateHeureFin']->getData()) {
                $dateFin = $filterForm['dateHeureFin']->getData();
            }

            $sortiesOrganisees = $filterForm['sortiesOrganisees']->getData();
            $sortiesInscrit = $filterForm['sortiesInscrit']->getData();
            $sortiesNonInscrit = $filterForm['sortiesNonInscrit']->getData();
            $sortiesPassees = $filterForm['sortiesPassees']->getData();

            $participantId = $this->getUser()->getId();

            $sorties = $sortieRepository->filtrerSorties($campus, $nom, $participantId, $dateDebut, $dateFin, $sortiesOrganisees, $sortiesInscrit, $sortiesNonInscrit, $sortiesPassees);

            $sorties = $gestionDesEtats->verificationEtats($sorties);

        } else {
            $sorties = $sortieRepository->filtrerSortieParEtat([7]);
            $sorties = $gestionDesEtats->verificationEtats($sorties);
        }

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

        // Redirige sur la page de list de sorties
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

        // Redirige sur la page de list de sorties
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

        $this->addFlash("sucess","La sortie à bien été publiée");

        return $this->redirectToRoute('sorties_list');

    }

    /**
     * @Route("/sorties/annuler/{id}", name="sorties_annuler")
     */
    public function annulerSortie(int $id, EntityManagerInterface $entityManager,
                                  EtatRepository $etatRepository,SortieRepository $sortieRepository,
                                    Request $request)
    {
        $sortie = $sortieRepository->findOneBy(["id"=>$id]);
        $sortie->setEtat($etatRepository->findOneBy(['id' => 6]));
        $sortie->setAnnulationMotifs($request->get("motif"));
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('sorties_list');
    }

}