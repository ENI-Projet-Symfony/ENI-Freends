<?php

namespace App\Util;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

class GestionDesEtats
{
    protected $entityManager;
    protected $sortieRepository;
    protected $etatRepository;

    public function __construct(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;

    }

    public function verificationEtats()
    {
        $em = $this->entityManager;
        $now = new \DateTime();
        $nowDay = new \DateTime($now->format('Y-M-d'). "00:00:00");
        $nowHour = new \DateTime($now->format('Y-M-d H:i:s'));

        $sortieOuvertes = $this->sortieRepository->filtrerSortieParEtat([1,7]);
        $etatClos = $this->etatRepository->find(['id' => 3]);
        $etatEnCours = $this->etatRepository->find(['id' => 4]);
        $etatPassee = $this->etatRepository->find(['id' => 5]);
        $etatArchive = $this->etatRepository->find(['id' => 7]);

        foreach ($sortieOuvertes as $sortie)
        {
            if($sortie->getDateLimiteInscription() <=  $nowDay)
            {
                $sortie->setEtat($etatClos);
                $em->persist($sortie);
            }

            if($sortie->getDateHeureDebut() <=  $nowHour)
            {
                $sortie->setEtat($etatEnCours);
                $em->persist($sortie);
            }

            $dureeActivite = $sortie->getDuree();
            $dateDebutActivite = clone $sortie->getDateHeureDebut();
            $dateFinActivite = $dateDebutActivite->add(new \DateInterval('P0Y0M0DT0H'. $dureeActivite . 'M0S'));

            if($dateFinActivite <=  $nowHour)
            {
                $sortie->setEtat($etatPassee);
                $em->persist($sortie);
            }

            $dateArchive = $dateFinActivite->add(new \DateInterval('P0Y1M0DT0H0M0S'));

            if($dateArchive <=  $nowDay)
            {
                $sortie->setEtat($etatArchive);
                $em->persist($sortie);
            }

        }
        $em->flush();
    }

}
