<?php

namespace App\Repository;

use App\Entity\Intervenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervenant>
 */
class IntervenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervenant::class);
    }

    /** @return Intervenant[] */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('iv')
            ->join('iv.utilisateur', 'u')
            ->leftJoin('iv.interventions', 'i')
            ->addSelect('u', 'i')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActifsCeMois(): int
    {
        $debut = new \DateTime('first day of this month midnight');
        $fin   = new \DateTime('last day of this month 23:59:59');

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.id)')
            ->join('i.interventions', 'iv')
            ->andWhere('iv.dateDebut >= :debut')
            ->andWhere('iv.dateDebut <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
