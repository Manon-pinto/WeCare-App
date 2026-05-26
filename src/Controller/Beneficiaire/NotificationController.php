<?php

namespace App\Controller\Beneficiaire;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications', methods: ['GET'])]
    public function liste(
        NotificationRepository $notificationRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        $notifications = $notificationRepo->findByUser($user);

        return $this->json([
            'nonLues' => $notificationRepo->countUnreadByUser($user),
            'notifications' => array_map(function ($n) {
                return [
                    'id' => $n->getId(),
                    'type' => $n->getType()?->value,
                    'lu' => $n->isLu(),
                    'compteRendu' => $n->getCompteRendu() ? [
                        'id' => $n->getCompteRendu()->getId(),
                        'intervention' => [
                            'id' => $n->getCompteRendu()->getIntervention()->getId(),
                            'intervenant' => $n->getCompteRendu()->getIntervention()->getIntervenant()->getUtilisateur()->getNom(),
                            'date' => $n->getCompteRendu()->getIntervention()->getDateDebut()?->format('d/m/Y'),
                            'type' => $n->getCompteRendu()->getIntervention()->getTypeIntervention()?->value,
                        ],
                    ] : null,
                ];
            }, $notifications),
        ]);
    }

    #[Route('/notifications/{id}/lire', name: 'notification_lire', methods: ['PATCH'])]
    public function marquerLu(
        int $id,
        NotificationRepository $notificationRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        $notification = $notificationRepo->find($id);
        if (!$notification || $notification->getUtilisateur() !== $user) {
            return $this->json(['error' => 'Notification non trouvée'], 404);
        }

        $notification->setLu(true);
        $em->flush();

        return $this->json(['message' => 'Notification marquée comme lue']);
    }

    #[Route('/notifications/lire-tout', name: 'notifications_lire_tout', methods: ['PATCH'])]
    public function marquerToutLu(
        NotificationRepository $notificationRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        $notifications = $notificationRepo->findUnreadByUser($user);
        foreach ($notifications as $n) {
            $n->setLu(true);
        }
        $em->flush();

        return $this->json(['message' => 'Toutes les notifications marquées comme lues']);
    }
}
