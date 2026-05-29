<?php

namespace App\Repository;

use App\Entity\Beneficiaire;
use App\Entity\Incident;
use App\Entity\Intervenant;
use App\Entity\Intervention;
use App\Enum\StatutIntervention;
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

    // ── Admin dashboard ──────────────────────────────────────────────────────

    /** @return Intervention[] */
    public function findForDate(\DateTimeInterface $date): array
    {
        $debut = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $fin   = new \DateTime($date->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('interv')
            ->join('interv.intervenant', 'iv')
            ->join('iv.utilisateur', 'uiv')
            ->join('interv.beneficiaire', 'ben')
            ->join('ben.utilisateur', 'uben')
            ->addSelect('iv', 'uiv', 'ben', 'uben')
            ->andWhere('interv.dateDebut >= :debut')
            ->andWhere('interv.dateDebut <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('interv.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAujourdhui(): int
    {
        $debut = new \DateTime('today midnight');
        $fin   = new \DateTime('today 23:59:59');

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.dateDebut >= :debut')
            ->andWhere('i.dateDebut <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTermineesAujourdhui(): int
    {
        $debut = new \DateTime('today midnight');
        $fin   = new \DateTime('today 23:59:59');

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.dateDebut >= :debut')
            ->andWhere('i.dateDebut <= :fin')
            ->andWhere('i.statut = :statut')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('statut', StatutIntervention::Terminee)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Intervention[] */
    public function findForWeek(\DateTimeInterface $weekStart): array
    {
        $debut = new \DateTime($weekStart->format('Y-m-d') . ' 00:00:00');
        $fin   = (clone $debut)->modify('+6 days')->setTime(23, 59, 59);

        return $this->createQueryBuilder('interv')
            ->join('interv.intervenant', 'iv')
            ->join('iv.utilisateur', 'uiv')
            ->join('interv.beneficiaire', 'ben')
            ->join('ben.utilisateur', 'uben')
            ->addSelect('iv', 'uiv', 'ben', 'uben')
            ->andWhere('interv.dateDebut >= :debut')
            ->andWhere('interv.dateDebut <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('interv.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActifsAujourdhui(): int
    {
        $debut = new \DateTime('today midnight');
        $fin   = new \DateTime('today 23:59:59');

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT IDENTITY(i.intervenant))')
            ->andWhere('i.dateDebut >= :debut')
            ->andWhere('i.dateDebut <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
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
