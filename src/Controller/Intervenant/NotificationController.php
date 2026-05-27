<?php

namespace App\Controller\Intervenant;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications_liste', methods: ['GET'])]
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
                            'beneficiaire' => $n->getCompteRendu()->getIntervention()->getBeneficiaire()->getUtilisateur()->getNom(),
                            'date' => $n->getCompteRendu()->getIntervention()->getDateDebut()?->format('d/m/Y'),
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
