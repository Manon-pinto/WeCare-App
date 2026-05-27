<?php

namespace App\Controller\Beneficiaire;

use App\Repository\InterventionRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        InterventionRepository $interventionRepo,
        NotificationRepository $notificationRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $now = new \DateTime();
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $prochaineIntervention = $interventionRepo->findNextByBeneficiaire($beneficiaire, $now);
        $interventionsAujourdhui = $interventionRepo->findByBeneficiaireAndDateRange($beneficiaire, $today, $tomorrow);

        $debutSemaine = new \DateTime('monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days')->setTime(23, 59, 59);
        $interventionsSemaine = $interventionRepo->findByBeneficiaireAndDateRange($beneficiaire, $debutSemaine, $finSemaine);

        $notificationsNonLues = $notificationRepo->countUnreadByUser($user);

        return $this->json([
            'beneficiaire' => [
                'nom' => $user->getNom(),
                'niveauRisque' => $beneficiaire->getNiveauRisque(),
            ],
            'prochaineIntervention' => $prochaineIntervention ? $this->serializeIntervention($prochaineIntervention) : null,
            'interventionsAujourdhui' => array_map(fn($i) => $this->serializeIntervention($i), $interventionsAujourdhui),
            'cetteSemaine' => array_map(fn($i) => $this->serializeIntervention($i), $interventionsSemaine),
            'notificationsNonLues' => $notificationsNonLues,
        ]);
    }

    private function serializeIntervention(\App\Entity\Intervention $i): array
    {
        $intervenant = $i->getIntervenant();
        $u = $intervenant->getUtilisateur();

        return [
            'id' => $i->getId(),
            'intervenant' => [
                'nom' => $u->getNom(),
                'specialite' => $intervenant->getSpecialite(),
                'telephone' => $intervenant->getTelephone(),
            ],
            'dateDebut' => $i->getDateDebut()?->format('d/m/Y H:i'),
            'dateFin' => $i->getDateFin()?->format('H:i'),
            'type' => $i->getTypeIntervention()?->value,
            'statut' => $i->getStatut()?->value,
            'observations' => $i->getObservations(),
        ];
    }
}
