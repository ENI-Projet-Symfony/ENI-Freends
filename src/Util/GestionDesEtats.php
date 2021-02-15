<?php

namespace App\Util;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Array_;

class GestionDesEtats
{
    protected $sortieRepository;
    protected $etatRepository;

    public function __construct(SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;

    }

    public function verificationEtats($sortieOuvertes)
    {
        $now = new \DateTime();
        $nowDay = new \DateTime($now->format('Y-M-d'). "00:00:00");
        $nowHour = new \DateTime($now->format('Y-M-d H:i:s'));

        $etatClos = $this->etatRepository->find(['id' => 3]);
        $etatEnCours = $this->etatRepository->find(['id' => 4]);
        $etatPassee = $this->etatRepository->find(['id' => 5]);
        $etatArchive = $this->etatRepository->find(['id' => 7]);


        if($sortieOuvertes instanceof Sortie)
        {
            $tampon = $sortieOuvertes;
            $sortieOuvertes = [];
            $sortieOuvertes [] = $tampon;
        }


        foreach ($sortieOuvertes as $sortie)
        {
            if($sortie->getDateLimiteInscription() <=  $nowDay)
            {
                $sortie->setEtat($etatClos);
            }

            if($sortie->getDateHeureDebut() <=  $nowHour)
            {
                $sortie->setEtat($etatEnCours);
            }

            $dureeActivite = $sortie->getDuree();
            $dateDebutActivite = clone $sortie->getDateHeureDebut();
            $dateFinActivite = $dateDebutActivite->add(new \DateInterval('P0Y0M0DT0H'. $dureeActivite . 'M0S'));

            if($dateFinActivite <=  $nowHour)
            {
                $sortie->setEtat($etatPassee);
            }

            $dateArchive = $dateFinActivite->add(new \DateInterval('P0Y1M0DT0H0M0S'));

            if($dateArchive <=  $nowDay)
            {
                $sortie->setEtat($etatArchive);
            }

        }

        return $sortieOuvertes;

    }

}
