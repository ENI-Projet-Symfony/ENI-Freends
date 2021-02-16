<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupeController extends AbstractController
{
    /**
     * @Route("/groupe/nouveaux", name="groupe_add")
     */
    public function nouveauxGroupe(): Response
    {
        return $this->render('groupe/nouveau.html.twig', [
        ]);
    }
}
