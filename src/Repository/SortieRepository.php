<?php

namespace App\Repository;

use App\Entity\Participants;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function filtrerSortieParEtat($etatsArray)
    {
        $qb = $this->createQueryBuilder('s');
        $expr = $qb->expr();

        $qb->where($expr->notIn('s.etat', ':values'));
        $qb->setParameter('values', $etatsArray);

        $qb = $qb->getQuery();
        return $qb->execute();
    }

    // filterSorties($campus, $keyword, $dateDebut, $dateFin, $sortiesOrganisees, $sortiesInscrit, $sortiesNonInscrit, $sortiesPassees
    public function filtrerSorties(
        $campus = null,
        $nom = null,
        $participantId = null,
        \DateTime $dateDebut = null,
        \DateTime $dateFin = null,
        bool $sortiesOrganisees = false,
        bool $sortiesInscrit = false,
        bool $sortiesNonInscrit = false,
        bool $sortiesPassees = false)
    {
        $qb = $this->createQueryBuilder('s');

        if ($campus != null){
            $qb ->andWhere('s.campus = :val_campus')
                ->setParameter('val_campus', $campus->getId());
        }
        if ($nom != null){
            $qb ->andWhere('s.nom LIKE :val_nom')
                ->setParameter('val_nom', '%' . $nom . '%');
        }
        if ($dateDebut != null) {
            $output = new \DateTime($dateDebut->format('Y-M-d'). "00:00:00");
            $qb ->andWhere('s.dateHeureDebut >= :val_dateDebut')
                ->setParameter('val_dateDebut', $output);
            dump($output);
        }
        if ($dateFin != null) {
            $output2 = new \DateTime($dateFin->format('Y-m-d')." 00:00:00");
            $qb ->andWhere('s.dateHeureDebut <= :val_dateFin')
                ->setParameter('val_dateFin', $output2);
        }
        if ($sortiesOrganisees){
            $qb ->andWhere('s.organisateur = :val_organisateur')
                ->setParameter('val_organisateur', $participantId);
        }
        if ($sortiesInscrit){
            $qb ->andWhere(':val_inscrit MEMBER OF s.participants')
                ->setParameter('val_inscrit', $participantId);
        }
        if ($sortiesNonInscrit){
            $qb ->andWhere(':val_nonInscrit NOT MEMBER OF s.participants')
                ->setParameter('val_nonInscrit', $participantId);
        }
        if ($sortiesPassees){
            $qb ->andWhere('s.dateHeureDebut <= :now')
                ->setParameter('now', date('Y-m-d H:i:s') );
        }

        $qb = $qb->getQuery();
        return $qb->execute();
    }


    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
