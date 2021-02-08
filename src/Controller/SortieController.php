<?php

namespace App\Controller;

use App\Form\SortieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/nouveau", name="sortie_add")
     */
    public function nouveaux(): Response
    {
        $form = $this->createForm(SortieType::class);

        dump("ok");
        return $this->render('sortie/nouveau.html.twig', [
            "formulaire" => $form->createView()
        ]);
    }
}
