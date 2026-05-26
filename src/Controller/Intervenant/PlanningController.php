<?php

namespace App\Controller\Intervenant;

use App\Repository\InterventionRepository;
use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'planning', methods: ['GET'])]
    public function planning(
        Request $request,
        InterventionRepository $interventionRepo,
        PlanningRepository $planningRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $semaine = $request->query->get('semaine');
        $dateDebut = $semaine ? new \DateTime($semaine) : new \DateTime('monday this week');
        $dateDebut->setTime(0, 0);
        $dateFin = (clone $dateDebut)->modify('+6 days')->setTime(23, 59, 59);

        $interventions = $interventionRepo->findByIntervenantAndDateRange($intervenant, $dateDebut, $dateFin);

        $jours = [];
        $cursor = clone $dateDebut;
        for ($i = 0; $i < 7; $i++) {
            $key = $cursor->format('Y-m-d');
            $jours[$key] = [
                'date' => $cursor->format('Y-m-d'),
                'jour' => $cursor->format('l'),
                'interventions' => [],
            ];
            $cursor->modify('+1 day');
        }

        foreach ($interventions as $intervention) {
            $key = $intervention->getDateDebut()->format('Y-m-d');
            if (isset($jours[$key])) {
                $b = $intervention->getBeneficiaire();
                $jours[$key]['interventions'][] = [
                    'id' => $intervention->getId(),
                    'beneficiaire' => [
                        'id' => $b->getId(),
                        'nom' => $b->getUtilisateur()->getNom(),
                        'adresse' => $b->getAdresse(),
                    ],
                    'dateDebut' => $intervention->getDateDebut()->format('H:i'),
                    'dateFin' => $intervention->getDateFin()->format('H:i'),
                    'type' => $intervention->getTypeIntervention()?->value,
                    'statut' => $intervention->getStatut()?->value,
                ];
            }
        }

        return $this->json([
            'semaine' => $dateDebut->format('d') . ' - ' . $dateFin->format('d F Y'),
            'jours' => array_values($jours),
        ]);
    }
}
