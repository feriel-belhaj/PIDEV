<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    public function findAllOrderedByIdDesc(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC') // Trie par ID décroissant (le plus grand en premier)
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedByIdDescQuery(): Query
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->getQuery();
    }
    public function getTypeCollabStats()
    {
        return $this->createQueryBuilder('c')
            ->select('c.typeCollab, COUNT(c.id) as type_count')
            ->groupBy('c.typeCollab')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Candidature[] Returns an array of Candidature objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Candidature
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}