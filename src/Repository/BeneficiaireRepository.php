<?php

namespace App\Repository;

use App\Entity\Administrateur;
use App\Entity\Beneficiaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Beneficiaire>
 */
class BeneficiaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Beneficiaire::class);
    }

    /** @return Beneficiaire[] — pour la carte : utilisateur + coords + risque seulement */
    public function findAllForMap(?Administrateur $admin = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('u.nom', 'ASC');
        if ($admin !== null) {
            $qb->andWhere('b.adminCreateur = :admin')->setParameter('admin', $admin);
        }
        return $qb->getQuery()->getResult();
    }

    /** @return Beneficiaire[] — id + nom uniquement, pour les dropdowns */
    public function findAllNames(?Administrateur $admin = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('u.nom', 'ASC');
        if ($admin !== null) {
            $qb->andWhere('b.adminCreateur = :admin')->setParameter('admin', $admin);
        }
        return $qb->getQuery()->getResult();
    }

    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Beneficiaire[] — eager-loads all relations needed for the patients page */
    public function findAllWithDetails(?Administrateur $admin = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->leftJoin('b.interventions', 'i')
            ->leftJoin('i.compteRendu', 'cr')
            ->leftJoin('i.intervenant', 'iv')
            ->leftJoin('iv.utilisateur', 'uiv')
            ->addSelect('u', 'i', 'cr', 'iv', 'uiv')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('i.dateDebut', 'DESC');
        if ($admin !== null) {
            $qb->andWhere('b.adminCreateur = :admin')->setParameter('admin', $admin);
        }
        return $qb->getQuery()->getResult();
    }
}
