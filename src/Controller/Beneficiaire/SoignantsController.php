<?php

namespace App\Controller\Beneficiaire;

use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class SoignantsController extends AbstractController
{
    #[Route('/soignants', name: 'soignants', methods: ['GET'])]
    public function liste(
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $intervenants = $interventionRepo->findDistinctIntervenantsByBeneficiaire($beneficiaire);

        $debutSemaine = new \DateTime('monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days')->setTime(23, 59, 59);

        return $this->json(array_map(function ($intervenant) use ($interventionRepo, $beneficiaire, $debutSemaine, $finSemaine) {
            $u = $intervenant->getUtilisateur();

            $interventionsSemaine = $interventionRepo->findByIntervenantBeneficiaireAndDateRange(
                $intervenant,
                $beneficiaire,
                $debutSemaine,
                $finSemaine
            );

            $jours = [];
            $cursor = clone $debutSemaine;
            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->format('Y-m-d');
                $jours[$key] = [
                    'date' => $cursor->format('Y-m-d'),
                    'jour' => substr($cursor->format('l'), 0, 3),
                    'heures' => null,
                ];
                $cursor->modify('+1 day');
            }

            foreach ($interventionsSemaine as $inv) {
                $key = $inv->getDateDebut()->format('Y-m-d');
                if (isset($jours[$key])) {
                    $jours[$key]['heures'] = $inv->getDateDebut()->format('H:i') . ' - ' . $inv->getDateFin()->format('H:i');
                    $jours[$key]['type'] = $inv->getTypeIntervention()?->value;
                }
            }

            return [
                'id' => $intervenant->getId(),
                'nom' => $u->getNom(),
                'specialite' => $intervenant->getSpecialite(),
                'telephone' => $intervenant->getTelephone(),
                'disponibilite' => $intervenant->isDisponibilite(),
                'planningCetteSemaine' => array_values($jours),
            ];
        }, $intervenants));
    }

    #[Route('/soignants/demande-wecare', name: 'soignants_demande', methods: ['POST'])]
    public function demandeWecare(): JsonResponse
    {
        // TODO: créer une demande formelle auprès de l'administrateur WeCare
        return $this->json(['message' => 'Demande envoyée à WeCare'], 201);
    }
}
