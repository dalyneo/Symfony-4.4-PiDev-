<?php

namespace App\Repository;

use App\Entity\Commenter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Commenter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commenter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commenter[]    findAll()
 * @method Commenter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commenter::class);
    }

    // /**
    //  * @return Commenter[] Returns an array of Commenter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Commenter
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function paginatedAnnonces($page, $limit){
        $query = $this->createQueryBuilder('a')
            ->setFirstResult(($page * $limit) - $limit)
            ->setMaxResults($limit);
        return $query->getQuery()->getResult();

        // On filtre les données
        // if($filters != null){
        //   $query->andWhere('a.forum IN(:cats)')
        //     ->setParameter(':cats', array_values($filters));
        //}

        //  $query->orderBy('a.date')
        //->setFirstResult(($page * $limit) - $limit)
        // ->setMaxResults($limit)
        // ;

    }

    public function getTotalAnnonces(){
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a)');

        // On filtre les données
        // if($filters != null){
        //   $query->andWhere('a.forum IN(:cats)')
        //     ->setParameter(':cats', array_values($filters));
        //}

        return $query->getQuery()->getSingleScalarResult();
    }

}
