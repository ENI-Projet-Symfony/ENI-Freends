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

            if($request->get("submit_type")==="1"){
                $sortie->setEtat($etatRepository->findOneBy([
                    'id' => "2"
                ]));
            }else if (0) {
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
        dump($form->getData());
        return $this->render('sortie/nouveau.html.twig', [
            "formulaire" => $form->createView()
        ]);
    }

    /**
     * @Route("/sortie/get/lieu", name="sortie_get_lieu")
     */
    public function getLieu(Request $request,SerializerInterface $serializer,LieuRepository $lieuRepository) : Response
    {

        if($request->get('id')) {

            $lieu = $lieuRepository->findOneBy([
                'id' => $request->get("id")
            ]);

            $lieu = [
                "longitude" => $lieu->getLongitude(),
                "latitude" => $lieu->getLatitude(),
                "rue" => $lieu->getRue(),
                "cp" => $lieu->getVille()->getCodePostal(),
            ];
        }else {
            $lieu = "ok";
        }

        $json = $serializer->serialize($lieu, 'json',['groups' => "lieu"]);

        return new JsonResponse($json, 200, [], true);
    }
}
