<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /** @return Notification[] */
    public function findByUser(Utilisateur $user): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.utilisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Notification[] */
    public function findUnreadByUser(Utilisateur $user): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.utilisateur = :user')
            ->andWhere('n.lu = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadByUser(Utilisateur $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.utilisateur = :user')
            ->andWhere('n.lu = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Notification[] — toutes notifs avec leur utilisateur, triées par date DESC */
    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('n')
            ->join('n.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
