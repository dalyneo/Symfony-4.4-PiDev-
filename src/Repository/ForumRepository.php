<?php

namespace App\Repository;

use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent ::__construct($registry, Forum::class);
    }

    // /**
    //  * @return Forum[] Returns an array of Forum objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Forum
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function OrderBysujetQB()
    {
        return $this -> createQueryBuilder('f')
            -> orderBy('f.date', 'ASC')
            -> getQuery() -> getResult();

    }

    function searchS($sujet)
    {
        return $this -> createQueryBuilder('f')
            -> where('f.sujet LIKE ?1')
            -> setParameter('1', '%'.$sujet . '%')
            -> orderBy('f.date', 'ASC')
            -> getQuery() -> getResult();
    }
    public function countByDate(){
        $query = $this->createQueryBuilder('a')
            ->select('SUBSTRING(a.date, 1, 10) as date, COUNT(a) as count')
            -> orderBy('a.date', 'ASC')
            ->groupBy('date')
        ;
        return $query->getQuery()->getResult();

    }

    public function paginatedAnnonces($page, $limit)
    {
        $query = $this -> createQueryBuilder('a')
            -> orderBy('a.date', 'ASC')
            -> setFirstResult(($page * $limit) - $limit)
            -> setMaxResults($limit);
        return $query -> getQuery() -> getResult();
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


    /*public function paginatedAnnoncesSearch($page, $limit, $sujet)
    {
        $query = $this -> createQueryBuilder('a')
            -> where('a.sujet LIKE ?1')
            -> setParameter('1', '%'.$sujet . '%')
            -> setFirstResult(($page * $limit) - $limit)
            -> setMaxResults($limit);
        return $query -> getQuery() -> getResult();
    }

    public function getTotalAnnoncesSearch($sujet){
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            -> where('a.sujet LIKE ?1')
            -> setParameter('1', '%'.$sujet . '%');

        // On filtre les données
        // if($filters != null){
        //   $query->andWhere('a.forum IN(:cats)')
        //     ->setParameter(':cats', array_values($filters));
        //}

        return $query->getQuery()->getSingleScalarResult();
    }*/





}