<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/sorties/get/lieu/info", name="sorties_get_lieu_info")
     */
    public function getLieuInfo(Request $request,SerializerInterface $serializer,LieuRepository $lieuRepository) : Response
    {


        if($request->get('id')) {

            $lieu = $lieuRepository->find($request->get("id"));

            if($lieu){
                $lieu = [
                    "longitude" => $lieu->getLongitude(),
                    "latitude" => $lieu->getLatitude(),
                    "rue" => $lieu->getRue(),
                    "cp" => $lieu->getVille()->getCodePostal(),
                ];
            }else{
                $lieu = [
                    "longitude" => "",
                    "latitude" => "",
                    "rue" => "",
                    "cp" => "",
                ];
            }


        }else {
            $lieu = [
                "longitude" => "",
                "latitude" => "",
                "rue" => "",
                "cp" => "",
            ];
        }

        $json = $serializer->serialize($lieu, 'json',['groups' => "lieu"]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/api/sorties/get/lieu", name="sorties_get_lieu")
     */
    public function getLieu(Request $request,SerializerInterface $serializer,LieuRepository $lieuRepository) : Response
    {

        if($request->get('id')) {

            $allLieu = $lieuRepository->findBy([
                'ville' => $request->get("id")
            ]);

        }

        return $this->render('sorties/selectLieu.html.twig', [
            "AllLieu" => $allLieu
        ]);
    }
}
