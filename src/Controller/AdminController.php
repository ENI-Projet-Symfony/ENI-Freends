<?php

namespace App\Controller;

use App\Form\FileUploadType;
use App\Util\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
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
    public function gestionUtilisateur(Request $request,FileUploader $fileUploader): Response
    {
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
/*        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form['upload_file']->getData();
            if ($file)
            {
                $file_name = $fileUploader->upload($file);
                if (null !== $file_name) // for example
                {
                    $directory = $uploadDirCsv;
                    $full_path = $directory.'/'.$file_name;

                }
                else
                {
                    // Oups, an error occured !!!
                }
            }*/

        return $this->render('admin/gestionutilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
