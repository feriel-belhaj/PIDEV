<?php

namespace App\Repository;

use App\Entity\FormationReservee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationReservee>
 *
 * @method FormationReservee|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormationReservee|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormationReservee[]    findAll()
 * @method FormationReservee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationReserveeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationReservee::class);
    }

    public function save(FormationReservee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FormationReservee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
} 