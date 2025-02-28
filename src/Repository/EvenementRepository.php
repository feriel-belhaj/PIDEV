<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByCategorie(?string $categorie)
    {
        $qb = $this->createQueryBuilder('e');
        
        if ($categorie && $categorie !== 'Toutes les catégories') {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }
        
        return $qb->orderBy('e.createdat', 'DESC')
                 ->getQuery()
                 ->getResult();
    }

    public function findByFilters(?string $categorie, string $tri, ?string $recherche)
    {
        $qb = $this->createQueryBuilder('e');
        
        // Filtre par catégorie
        if ($categorie && $categorie !== 'Toutes les catégories') {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }
        
        // Recherche par titre
        if ($recherche) {
            $qb->andWhere('e.titre LIKE :recherche')
               ->setParameter('recherche', '%'.$recherche.'%');
        }
        
        // Tri
        switch ($tri) {
            case 'popularite':
                $qb->leftJoin('e.dons', 'd')
                   ->groupBy('e.id')
                   ->orderBy('COUNT(d.id)', 'DESC');
                break;
            case 'date_debut':
                $qb->orderBy('e.startdate', 'ASC');
                break;
            case 'montant':
                $qb->orderBy('e.collectedamount', 'DESC');
                break;
            default: // date_creation
                $qb->orderBy('e.createdat', 'DESC');
        }
        
        return $qb->getQuery()->getResult();
    }

    public function findByFiltersWithPagination(?string $categorie, string $tri, ?string $recherche, int $page = 1, int $limit = 6)
    {
        $qb = $this->createQueryBuilder('e');
        
        // Filtre par catégorie
        if ($categorie && $categorie !== 'Toutes les catégories') {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }
        
        // Recherche par titre
        if ($recherche) {
            $qb->andWhere('e.titre LIKE :recherche')
               ->setParameter('recherche', '%'.$recherche.'%');
        }
        
        // Tri
        switch ($tri) {
            case 'popularite':
                $qb->leftJoin('e.dons', 'd')
                   ->groupBy('e.id')
                   ->orderBy('COUNT(d.id)', 'DESC');
                break;
            case 'date_debut':
                $qb->orderBy('e.startdate', 'ASC');
                break;
            case 'montant':
                $qb->orderBy('e.collectedamount', 'DESC');
                break;
            default: // date_creation
                $qb->orderBy('e.createdat', 'DESC');
        }
        
        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);
        
        return $qb->getQuery()->getResult();
    }

    public function countTotal(?string $categorie, ?string $recherche): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');
        
        if ($categorie && $categorie !== 'Toutes les catégories') {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }
        
        if ($recherche) {
            $qb->andWhere('e.titre LIKE :recherche')
               ->setParameter('recherche', '%'.$recherche.'%');
        }
        
        return $qb->getQuery()->getSingleScalarResult();
    }
}
