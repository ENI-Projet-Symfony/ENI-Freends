<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\FileUploadType;
use App\Form\UserInformationType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Util\FileUploader;
use claviska\SimpleImage;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use mysql_xdevapi\Exception;
use PHPTokenGenerator\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AdminController extends AbstractController
{
    private $encoder;

    /**
     * @Route("/admin/backoffice", name="admin_backoffice")
     */
    public function backOffice(): Response
    {
        return $this->redirectToRoute('admin_gestion_utilisateur');
    }

    /**
     * @Route("/admin/gestion/utilisateur/", name="admin_gestion_utilisateur")
     */
    public function listeUtilisateur(ParticipantRepository $participantRepository)
    {
        $allParticipant = $participantRepository->findAll();
        return $this->render('admin/gestionutilisateur.html.twig', [
            'allParticipant' => $allParticipant
        ]);

    }

    /**
     * @Route("/admin/gestion/utilisateur/info/{id}", name="admin_gestion_utilisateur_infos")
     */
    public function infoUtilisateur(int $id,ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->find($id);
        return $this->render('admin/infoUtilisateur.html.twig', [
            'Participant' => $participant
        ]);
    }


    /**
     * @Route("/admin/gestion/utilisateur/ajouter", name="admin_gestion_utilisateur_ajouter")
     */
    public function ajouterUtilisateur
    (Request $request,FileUploader $fileUploader, String $uploadDirCsv,
     EntityManagerInterface $entityManager,
     CampusRepository $campusRepository,UserPasswordEncoderInterface $encoder,
     string $uploadDirImg): Response
    {
        $participant = new Participant();

        $formCSV = $this->createForm(FileUploadType::class);
        $formManual = $this->createForm(UserInformationType::class,$participant);

        $formCSV->handleRequest($request);
        $formManual->handleRequest($request);



        $this->encoder = $encoder;

        if ($formCSV->isSubmitted() && $formCSV->isValid())
        {
            $file = $formCSV['upload_file']->getData();
            if ($file)
            {
                $file_name = $fileUploader->upload($file);
                if (null !== $file_name) // for example
                {
                    $directory = $uploadDirCsv;
                    $full_path = $directory . '/' . $file_name;

                    $reader = Reader::createFromPath($full_path);
                    $reader->setDelimiter(';');
                    $reader->setHeaderOffset(0);
                    $results = $reader->getRecords();

                    $year = date('Y');

                    try {
                        foreach ($results as $row) {

                            $campus = $campusRepository->find(['id'=> $row['Campus']]);

                            $participant = (new Participant());

                            $participant
                                ->setPseudo($row['Pseudo'])
                                ->setRoles(['ROLE_USER'])
                                ->setNom($row['Nom'])
                                ->setPrenom($row['Prenom'])
                                ->setPassword($this->encoder->encodePassword($participant,$row['Prenom'] . $row['Nom'] . $year))
                                ->setMail($row['Prenom'] . $row['Nom'] . $year .'@campus-eni.fr')
                                ->setTelephone($row['Telephone'])
                                ->setActif(true)
                                ->setCampus($campus);

                            $entityManager->persist($participant);
                        }
                        $entityManager->flush();

                        unlink($full_path);

                        $this->addFlash('success','Vos données on été ajoutées en base');
                    }
                    catch (\Exception $errorException)
                    {
                        unlink($full_path);
                        $this->addFlash('warning','Echec du téléchargement du fichier');
                    }
                }
                else {
                    $this->addFlash('warning','Echec du téléchargement du fichier');
                }
            }

            return $this->redirectToRoute('admin_gestion_utilisateur_ajouter');
        }
        else if ( $formManual->isSubmitted() && $formManual->isValid()){
            if($formManual->get('password')->getData())
            {
                $participant->setPassword($encoder->encodePassword($participant, $formManual->get('password')->getData()));
            }
            /** @var UploadedFile $photo */
            $photo = $formManual->get('photo')->getData();

            if($photo){

                $generator = new TokenGenerator();
                $nomFichier = $generator->generate() . "." . $photo->guessExtension();
                $photoFinale = new SimpleImage();
                $photoFinale
                    ->fromFile($photo)
                    ->autoOrient()
                    ->bestFit(200,200)
                    ->toFile($uploadDirImg . $nomFichier);
                if($participant->getNomFichierPhoto()) {
                    try {
                        unlink($uploadDirImg . $participant->getNomFichierPhoto());
                    } catch (\ErrorException $ex) {
                        // catch vide, au cas où un nom de fichier est présent en base mais l'image n'est pas sur le serveur
                    }
                }
                $participant->setNomFichierPhoto($nomFichier);
            }
            $participant->setActif(true);
            $participant->setRoles(array("ROLE_USER"));
            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash('success','Vos données on été ajoutées en base');

            return $this->redirectToRoute('admin_gestion_utilisateur_ajouter');
        }
        return $this->render('admin/ajoututilisateur.html.twig', [
            'formCSV' => $formCSV->createView(),
            'formManual' => $formManual->createView(),
        ]);
    }


    /**
     * @Route("/admin/gestion/lieux/", name="admin_gestion_lieux")
     */
    public function listeLieux(LieuRepository $lieuRepository)
    {
        $allLieux = $lieuRepository->findAll();
        return $this->render('admin/gestionlieux.html.twig', [
            'lieux' => $allLieux
        ]);
    }

    /**
     * @Route("/admin/gestion/lieux/info/{id}", name="admin_gestion_lieux_infos")
     */
    public function infoLieux(int $id,LieuRepository $lieuRepository)
    {
        $lieu = $lieuRepository->find($id);
        return $this->render('admin/infoLieu.html.twig', [
            'lieu' => $lieu
        ]);
    }

    /**
     * @Route("/admin/gestion/utilisateur/{id}/activer", name="admin_activer_utilisateur", methods={"GET"})
     */
    public function activerUtilisateur(int $id, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
        $participant = $participantRepository->findOneBy(['id'=>$id]);
        $participant->setActif(true);

        $entityManager->persist($participant);
        $entityManager->flush();

        // Ajoute un message de succès
        $this->addFlash("success","Utilisateur activé avec succès");

        // Redirige sur la page de list de sorties
        return $this->redirectToRoute('admin_gestion_utilisateur');
    }


    /**
     * @Route("/admin/gestion/utilisateur/{id}/desactiver", name="admin_desactiver_utilisateur", methods={"GET"})
     */
    public function desactiverUtilisateur(int $id, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
        $participant = $participantRepository->findOneBy(['id'=>$id]);
        $participant->setActif(false);
        $entityManager->persist($participant);
        $entityManager->flush();

        // Ajoute un message de succès
        $this->addFlash("success","Utilisateur désactivé avec succès");

        // Redirige sur la page de list de sorties
        return $this->redirectToRoute('admin_gestion_utilisateur');
    }

    /**
     * @Route("/admin/gestion/utilisateur/activer", name="admin_activer_utilisateur_multi", methods={"GET"})
     */
    public function activerAllUtilisateur(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        $data =$request->get('data');
        $tabId = $data['liste_utilisateurs'];

        foreach ($tabId as $d) {
            // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
            $participant = $participantRepository->find($d);
            $participant->setActif(true);

            $entityManager->persist($participant);
        }
        $entityManager->flush();

        // Ajoute un message de succès
        $this->addFlash("success","Utilisateur activé avec succès");

        return new JsonResponse([
            "status" => "deleted"
        ], 200);
    }

    /**
     * @Route("/admin/gestion/utilisateur/desactiver", name="admin_desactiver_utilisateur_multi", methods={"GET"})
     */
    public function desactiverAllUtilisateur(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        $data =$request->get('data');
        $tabId = $data['liste_utilisateurs'];

        foreach ($tabId as $d) {
            // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
            $participant = $participantRepository->find($d);
            $participant->setActif(false);
            $entityManager->persist($participant);
        }
        $entityManager->flush();

        // Ajoute un message de succès
        $this->addFlash("success","Utilisateur désactivé avec succès");

        return new JsonResponse([
            "status" => "deleted"
        ], 200);
    }

    /**
     * @Route("/admin/gestion/utilisateur/suppression_multi", name="admin_suppression_utilisateur_multi", methods={"GET"})
     */
    public function suppressionAllUtilisateurs(Request $request, ParticipantRepository $participantRepository, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $data =$request->get('data');
        $tabId = $data['liste_utilisateurs'];

        foreach ($tabId as $d) {
            // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
            $participant = $participantRepository->find($d);

            // Si cette sortie n'existe pas en BDD
            if (!$participant){
                //alors on déclenche une 404
                throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
            }

            // Définir l'administrateur qui supprime un utilisateur en tant qu'organisteur des sorties existantes de cet utilisateur
            // Récupère l'ID de l'administrateur
            $adminId = $this->getUser();

            // Récupère toutes les sorties organisées par l'utilisateur
            $sorties = $sortieRepository->findBy([
                'organisateur' => $d
            ]);
            // Défini l'administrateur en tant qu'organisateur de ces sorties
            foreach ($sorties as $sortie)
            {
                $sortie->setOrganisateur($adminId);
                $entityManager->persist($sortie);
            }
            $entityManager->flush();

            // Supprime l'utilisateur de la BDD
            // $qbuser = $participantRepository->createQueryBuilder('p');
            // $qbuser->delete();
            // $qbuser->where('p.id = :val_id');
            // $qbuser->setParameter('val_id', $d);
            // $query = $qbuser->getQuery();
            // $result = $query->getResult();

            $entityManager->remove($participant);
            $entityManager->flush();
        }

        // Ajoute un message de succès
        $this->addFlash("success","Utilisateur supprimé avec succès");

        return new JsonResponse([
            "status" => "deleted"
        ], 200);
    }

    /**
     * @Route("/admin/gestion/utilisateur/{id}/suppression", name="admin_suppression_utilisateur", methods={"GET"})
     */
    public function suppressionUtilisateur(int $id, EntityManagerInterface $entityManager,
                                   SortieRepository $sortieRepository,
                                   ParticipantRepository $participantRepository): Response
    {
        // Récupère l'Id du participant et la sortie à laquelle on s'inscrit
        $participant = $participantRepository->findBy([
            'id' => $id
        ]);

        // Si cette sortie n'existe pas en BDD
        if (!$participant){
            //alors on déclenche une 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // Définir l'administrateur qui supprime un utilisateur en tant qu'organisteur des sorties existantes de cet utilisateur
        // Récupère l'ID de l'administrateur
        $adminId = $this->getUser();

        // Récupère toutes les sorties organisées par l'utilisateur
        $sorties = $sortieRepository->findBy([
           'organisateur' => $id
        ]);
        // Défini l'administrateur en tant qu'organisateur de ces sorties
        foreach ($sorties as $sortie)
        {
            $sortie->setOrganisateur($adminId);
            $entityManager->persist($sortie);
        }
        $entityManager->flush();

        // Supprime l'utilisateur de la BDD
        $qbuser = $participantRepository->createQueryBuilder('p');
        $qbuser->delete();
        $qbuser->where('p.id = :val_id');
        $qbuser->setParameter('val_id', $id);
        $query = $qbuser->getQuery();
        $result = $query->getResult();

        // Ajoute un message de succès
        $this->addFlash("success","Utilisateur supprimé avec succès");

        // Redirige sur la page de list de sorties
        return $this->redirectToRoute('admin_gestion_utilisateur');
    }

    /**
     * @Route("/admin/gestion/sorties/", name="admin_gestion_sorties")
     */
    public function listeSorties(SortieRepository $sortieRepository)
    {
        $allSorties = $sortieRepository->findAll();
        return $this->render('admin/gestionsorties.html.twig', [
            'sorties' => $allSorties
        ]);
    }

    /**
     * @Route("/admin/gestion/sorties/annuler/{id}", name="admin_annuler_sortie")
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

        return $this->redirectToRoute('admin_gestion_sorties');
    }
}
