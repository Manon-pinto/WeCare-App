<?php

namespace App\Repository;

use App\Entity\Administrateur;
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

    /** @return Aidant[] — charge beneficiaire + utilisateur + soignantReferent, sans interventions */
    public function findAllWithDetails(?Administrateur $admin = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.beneficiaire', 'b')
            ->join('b.utilisateur', 'ub')
            ->leftJoin('b.soignantReferent', 'sr')
            ->leftJoin('sr.utilisateur', 'usr')
            ->addSelect('b', 'ub', 'sr', 'usr')
            ->orderBy('a.nom', 'ASC');
        if ($admin !== null) {
            $qb->andWhere('b.adminCreateur = :admin')->setParameter('admin', $admin);
        }
        return $qb->getQuery()->getResult();
    }
}
