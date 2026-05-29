<?php

namespace App\Repository;

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

    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Beneficiaire[] — eager-loads all relations needed for the patients page */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.utilisateur', 'u')
            ->leftJoin('b.interventions', 'i')
            ->leftJoin('i.compteRendu', 'cr')
            ->leftJoin('i.intervenant', 'iv')
            ->leftJoin('iv.utilisateur', 'uiv')
            ->addSelect('u', 'i', 'cr', 'iv', 'uiv')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('i.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
