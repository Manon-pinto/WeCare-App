<?php

namespace App\Repository;

use App\Entity\Beneficiaire;
use App\Entity\Message;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /** @return Message[] */
    public function findConversationsByBeneficiaire(Utilisateur $user, Beneficiaire $beneficiaire): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.beneficiaire = :beneficiaire OR m.expediteur = :user OR m.destinataire = :user')
            ->setParameter('beneficiaire', $beneficiaire)
            ->setParameter('user', $user)
            ->orderBy('m.dateEnvoi', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadByUser(Utilisateur $user): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.destinataire = :user')
            ->andWhere('m.lu = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
