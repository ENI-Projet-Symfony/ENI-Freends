<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\FileUploadType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Util\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    private $encoder;

    /**
     * @Route("/admin/backoffice", name="admin_backoffice")
     */
    public function backOffice(): Response
    {
        return $this->render('admin/homeBackOffice.html.twig', []);
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
     CampusRepository $campusRepository,UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
        $this->encoder = $encoder;

        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form['upload_file']->getData();
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

                        $this->addFlash('succes','Vos données on été ajoutées en base');
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
        }
        return $this->render('admin/ajoututilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
