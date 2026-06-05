<?php

namespace App\Repository;

use App\Entity\Incident;
use App\Enum\StatutIncident;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Incident>
 */
class IncidentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Incident::class);
    }

    /** @return Incident[] */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.statut IN (:statuts)')
            ->setParameter('statuts', [StatutIncident::Signale, StatutIncident::EnTraitement])
            ->orderBy('i.dateSignalement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Incident[] — eager-loads intervention → bénéficiaire + intervenant */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('inc')
            ->join('inc.intervention', 'i')
            ->join('i.beneficiaire', 'ben')
            ->join('ben.utilisateur', 'uben')
            ->join('i.intervenant', 'iv')
            ->join('iv.utilisateur', 'uiv')
            ->addSelect('i', 'ben', 'uben', 'iv', 'uiv')
            ->orderBy('inc.dateSignalement', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
