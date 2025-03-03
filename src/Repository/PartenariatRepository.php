<?php

namespace App\Repository;

use App\Entity\Partenariat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Partenariat>
 */
class PartenariatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partenariat::class);
    }

    public function findAllOrderedByIdDescQuery(?string $dateDebut, ?string $dateFin): Query
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');

        if ($dateDebut) {
            $qb->andWhere('p.dateDebut >= :dateDebut')
               ->setParameter('dateDebut', $dateDebut);
        }
        if ($dateFin) {
            $qb->andWhere('p.dateFin <= :dateFin')
               ->setParameter('dateFin', $dateFin);
        }

        return $qb->getQuery();
    }
}