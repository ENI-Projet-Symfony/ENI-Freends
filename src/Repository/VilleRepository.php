<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }
    public function getVillesEtNbrLieux($page = 1){
        $maxResults = 15;
        $firstResult = ($page - 1 ) * $maxResults;
        $dql = 'SELECT v.id,v.nom,v.codePostal, COUNT(l.id) nbrLieux
                FROM App\Entity\Ville v left JOIN v.lieus l
                GROUP BY v.id';
        $dqlCount = 'SELECT COUNT(v.id) FROM App\Entity\Ville v';
        $em = $this->getEntityManager();
        //récupération des résultats
        $results= $em->createQuery($dql)->setMaxResults($maxResults)->setFirstResult($firstResult)->getResult();

        $totalResultsCount = $em->createQuery($dqlCount)->getResult();


        $data = [
            "numberOfResultsPerPage" => $maxResults,
            "totalResultsCount" => $totalResultsCount,
            "currentPage" => $page,
            "results" => $results
        ];
        return $data;
    }

    // /**
    //  * @return Ville[] Returns an array of Ville objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ville
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
