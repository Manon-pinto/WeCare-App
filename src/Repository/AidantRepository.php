<?php

namespace App\Repository;

use App\Entity\Aidant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Aidant>
 */
class AidantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aidant::class);
    }

    /** @return Aidant[] */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.beneficiaire', 'b')
            ->join('b.utilisateur', 'ub')
            ->leftJoin('b.interventions', 'i')
            ->leftJoin('i.intervenant', 'iv')
            ->leftJoin('iv.utilisateur', 'uiv')
            ->addSelect('b', 'ub', 'i', 'iv', 'uiv')
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
