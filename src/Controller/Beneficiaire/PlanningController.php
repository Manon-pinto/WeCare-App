<?php

namespace App\Controller\Beneficiaire;

use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'planning', methods: ['GET'])]
    public function planning(
        Request $request,
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $semaine = $request->query->get('semaine');
        $dateDebut = $semaine ? new \DateTime($semaine) : new \DateTime('monday this week');
        $dateDebut->setTime(0, 0);
        $dateFin = (clone $dateDebut)->modify('+6 days')->setTime(23, 59, 59);

        $interventions = $interventionRepo->findByBeneficiaireAndDateRange($beneficiaire, $dateDebut, $dateFin);

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
                $intervenant = $intervention->getIntervenant();
                $jours[$key]['interventions'][] = [
                    'id' => $intervention->getId(),
                    'intervenant' => [
                        'nom' => $intervenant->getUtilisateur()->getNom(),
                        'specialite' => $intervenant->getSpecialite(),
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

    #[Route('/planning/demande-modification', name: 'planning_demande_modification', methods: ['POST'])]
    public function demandeModification(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['message'])) {
            return $this->json(['error' => 'Le message est obligatoire'], 422);
        }

        // TODO: envoyer un message/notification à l'administrateur
        return $this->json(['message' => 'Demande de modification envoyée à WeCare'], 201);
    }
}
