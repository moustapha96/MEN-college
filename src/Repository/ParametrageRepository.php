<?php

namespace App\Repository;

use App\Entity\Parametrage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parametrage>
 *
 * @method Parametrage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parametrage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parametrage[]    findAll()
 * @method Parametrage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParametrageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametrage::class);
    }

//    /**
//     * @return Parametrage[] Returns an array of Parametrage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Parametrage
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
