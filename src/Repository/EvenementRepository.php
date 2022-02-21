<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    function RechercheNom($nom){
        return $this->createQueryBuilder('e')
            ->where('e.nom LIKE :nom')
            ->setParameter('nom','%'.$nom.'%')
            ->getQuery()->getResult();
    }


    function OrderByNom(){
        return $this->createQueryBuilder('e')
            ->orderBy('e.nom','ASC')
            ->setMaxResults(500)->getQuery()->getResult();
    }



    function TotalEvent()
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getResult();
    }
}
