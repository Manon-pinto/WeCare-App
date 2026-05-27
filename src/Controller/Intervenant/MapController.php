<?php

namespace App\Controller\Intervenant;

use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MapController extends AbstractController
{
    #[Route('/intervenant/map', name: 'intervenant_map', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('intervenant/map.html.twig');
    }

    #[Route('/api/intervenant/map', name: 'api_intervenant_map', methods: ['GET'])]
    public function map(
        Request $request,
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $dateStr = $request->query->get('date');
        $date = $dateStr ? new \DateTime($dateStr) : new \DateTime('today');
        $dateFin = (clone $date)->setTime(23, 59, 59);
        $date->setTime(0, 0);

        $vue = $request->query->get('vue', 'jour');

        if ($vue === 'semaine') {
            $date->modify('monday this week');
            $dateFin = (clone $date)->modify('+6 days')->setTime(23, 59, 59);
        }

        $interventions = $interventionRepo->findByIntervenantAndDateRange($intervenant, $date, $dateFin);

        $tournee = array_map(function ($i) {
            $b = $i->getBeneficiaire();
            return [
                'id' => $i->getId(),
                'beneficiaire' => [
                    'nom' => $b->getUtilisateur()->getNom(),
                    'adresse' => $b->getAdresse(),
                    'latitude' => (float) $b->getLatitude(),
                    'longitude' => (float) $b->getLongitude(),
                    'pathologie' => $b->getPathologie(),
                    'niveauRisque' => $b->getNiveauRisque(),
                ],
                'dateDebut' => $i->getDateDebut()?->format('H:i'),
                'dateFin' => $i->getDateFin()?->format('H:i'),
                'type' => $i->getTypeIntervention()?->value,
                'statut' => $i->getStatut()?->value,
            ];
        }, $interventions);

        return $this->json([
            'date' => $date->format('d/m/Y'),
            'nbVisites' => count($interventions),
            'tournee' => $tournee,
        ]);
    }
}
