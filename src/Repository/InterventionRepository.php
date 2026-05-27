<?php

namespace App\Repository;

use App\Entity\Beneficiaire;
use App\Entity\Incident;
use App\Entity\Intervenant;
use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    // ── Intervenant ──────────────────────────────────────────────────────────

    /** @return Intervention[] */
    public function findByIntervenantAndDateRange(
        Intervenant $intervenant,
        \DateTimeInterface $debut,
        \DateTimeInterface $fin
    ): array {
        return $this->createQueryBuilder('i')
            ->andWhere('i.intervenant = :intervenant')
            ->andWhere('i.dateDebut >= :debut')
            ->andWhere('i.dateDebut <= :fin')
            ->setParameter('intervenant', $intervenant)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('i.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Intervention[] */
    public function findPatientsWithAlerts(Intervenant $intervenant): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.beneficiaire', 'b')
            ->andWhere('i.intervenant = :intervenant')
            ->andWhere('b.niveauRisque IN (:niveaux)')
            ->andWhere('i.dateDebut >= :today')
            ->setParameter('intervenant', $intervenant)
            ->setParameter('niveaux', ['élevé', 'critique'])
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('i.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Beneficiaire[] */
    public function findDistinctBeneficiairesByIntervenant(Intervenant $intervenant): array
    {
        $rows = $this->createQueryBuilder('i')
            ->select('DISTINCT IDENTITY(i.beneficiaire) as bid')
            ->andWhere('i.intervenant = :intervenant')
            ->setParameter('intervenant', $intervenant)
            ->getQuery()
            ->getScalarResult();

        $ids = array_column($rows, 'bid');
        if (empty($ids)) {
            return [];
        }

        return $this->getEntityManager()
            ->getRepository(Beneficiaire::class)
            ->findBy(['id' => $ids]);
    }

    /** @return Intervention[] */
    public function findUpcomingByIntervenantAndBeneficiaire(
        Intervenant $intervenant,
        Beneficiaire $beneficiaire,
        int $limit = 3
    ): array {
        return $this->createQueryBuilder('i')
            ->andWhere('i.intervenant = :intervenant')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->andWhere('i.dateDebut >= :now')
            ->setParameter('intervenant', $intervenant)
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('now', new \DateTime())
            ->orderBy('i.dateDebut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Intervention[] */
    public function findPastByIntervenantAndBeneficiaire(
        Intervenant $intervenant,
        Beneficiaire $beneficiaire,
        int $limit = 5
    ): array {
        return $this->createQueryBuilder('i')
            ->andWhere('i.intervenant = :intervenant')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->andWhere('i.dateDebut < :now')
            ->setParameter('intervenant', $intervenant)
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('now', new \DateTime())
            ->orderBy('i.dateDebut', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Intervention[] */
    public function findWithCompteRenduByIntervenant(Intervenant $intervenant): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.compteRendu', 'cr')
            ->addSelect('cr')
            ->andWhere('i.intervenant = :intervenant')
            ->andWhere('i.dateDebut < :now')
            ->setParameter('intervenant', $intervenant)
            ->setParameter('now', new \DateTime())
            ->orderBy('i.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Incident[] */
    public function findIncidentsByIntervenant(Intervenant $intervenant): array
    {
        return $this->getEntityManager()
            ->getRepository(Incident::class)
            ->createQueryBuilder('inc')
            ->join('inc.intervention', 'i')
            ->andWhere('i.intervenant = :intervenant')
            ->setParameter('intervenant', $intervenant)
            ->orderBy('inc.dateSignalement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ── Bénéficiaire ─────────────────────────────────────────────────────────

    public function findNextByBeneficiaire(Beneficiaire $beneficiaire, \DateTimeInterface $from): ?Intervention
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->andWhere('i.dateDebut >= :from')
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('from', $from)
            ->orderBy('i.dateDebut', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Intervention[] */
    public function findByBeneficiaireAndDateRange(
        Beneficiaire $beneficiaire,
        \DateTimeInterface $debut,
        \DateTimeInterface $fin
    ): array {
        return $this->createQueryBuilder('i')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->andWhere('i.dateDebut >= :debut')
            ->andWhere('i.dateDebut <= :fin')
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('i.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Intervenant[] */
    public function findDistinctIntervenantsByBeneficiaire(Beneficiaire $beneficiaire): array
    {
        $rows = $this->createQueryBuilder('i')
            ->select('DISTINCT IDENTITY(i.intervenant) as iid')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->setParameter('beneficiaire', $beneficiaire)
            ->getQuery()
            ->getScalarResult();

        $ids = array_column($rows, 'iid');
        if (empty($ids)) {
            return [];
        }

        return $this->getEntityManager()
            ->getRepository(\App\Entity\Intervenant::class)
            ->findBy(['id' => $ids]);
    }

    /** @return Intervention[] */
    public function findByIntervenantBeneficiaireAndDateRange(
        Intervenant $intervenant,
        Beneficiaire $beneficiaire,
        \DateTimeInterface $debut,
        \DateTimeInterface $fin
    ): array {
        return $this->createQueryBuilder('i')
            ->andWhere('i.intervenant = :intervenant')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->andWhere('i.dateDebut >= :debut')
            ->andWhere('i.dateDebut <= :fin')
            ->setParameter('intervenant', $intervenant)
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('i.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Intervention[] */
    public function findWithCompteRenduByBeneficiaire(Beneficiaire $beneficiaire): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.compteRendu', 'cr')
            ->addSelect('cr')
            ->andWhere('i.beneficiaire = :beneficiaire')
            ->andWhere('i.dateDebut < :now')
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('now', new \DateTime())
            ->orderBy('i.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
