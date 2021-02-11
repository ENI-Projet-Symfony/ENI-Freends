<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\FileUploadType;
use App\Repository\CampusRepository;
use App\Util\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
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
        return $this->render('admin/backOffice.html.twig', [
        ]);
    }

    /**
     * @Route("/admin/gestionutilisateur", name="admin_gestion_utilisateur")
     */
    public function gestionUtilisateur
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
                    dump($year);

                    foreach ($results as $row) {
                        dump($row);
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
                else {
                    $this->addFlash('warning','Echec du téléchargement du fichier');
                }
            }
        }
        return $this->render('admin/gestionutilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
