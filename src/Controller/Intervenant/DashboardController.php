<?php

namespace App\Controller\Intervenant;

use App\Entity\Intervenant;
use App\Repository\InterventionRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        InterventionRepository $interventionRepo,
        NotificationRepository $notificationRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $interventionsAujourdhui = $interventionRepo->findByIntervenantAndDateRange(
            $intervenant,
            $today,
            $tomorrow
        );

        $notificationsNonLues = $notificationRepo->countUnreadByUser($user);

        $pointsAttention = $interventionRepo->findPatientsWithAlerts($intervenant);

        return $this->json([
            'intervenant' => [
                'nom' => $user->getNom(),
                'specialite' => $intervenant->getSpecialite(),
            ],
            'dateAujourdhui' => $today->format('l d F Y'),
            'interventionsAujourdhui' => array_map(
                fn($i) => $this->serializeIntervention($i),
                $interventionsAujourdhui
            ),
            'nbPointsAttention' => count($pointsAttention),
            'pointsAttention' => array_map(
                fn($i) => $this->serializeIntervention($i),
                $pointsAttention
            ),
            'notificationsNonLues' => $notificationsNonLues,
        ]);
    }

    private function serializeIntervention(\App\Entity\Intervention $i): array
    {
        $b = $i->getBeneficiaire();
        $u = $b->getUtilisateur();

        return [
            'id' => $i->getId(),
            'beneficiaire' => [
                'id' => $b->getId(),
                'nom' => $u->getNom(),
                'adresse' => $b->getAdresse(),
                'pathologie' => $b->getPathologie(),
                'niveauRisque' => $b->getNiveauRisque(),
            ],
            'dateDebut' => $i->getDateDebut()?->format('H:i'),
            'dateFin' => $i->getDateFin()?->format('H:i'),
            'type' => $i->getTypeIntervention()?->value,
            'statut' => $i->getStatut()?->value,
        ];
    }
}
