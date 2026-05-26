<?php

namespace App\Controller\Beneficiaire;

use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class CompteRenduController extends AbstractController
{
    #[Route('/comptes-rendus', name: 'comptes_rendus', methods: ['GET'])]
    public function liste(
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $interventions = $interventionRepo->findWithCompteRenduByBeneficiaire($beneficiaire);

        return $this->json(array_map(function ($i) {
            $cr = $i->getCompteRendu();
            $intervenant = $i->getIntervenant();

            return [
                'interventionId' => $i->getId(),
                'intervenant' => $intervenant->getUtilisateur()->getNom(),
                'specialite' => $intervenant->getSpecialite(),
                'date' => $i->getDateDebut()?->format('d/m/Y'),
                'type' => $i->getTypeIntervention()?->value,
                'compteRendu' => $cr ? [
                    'id' => $cr->getId(),
                    'dateRedaction' => $cr->getDateRedaction()?->format('d/m/Y'),
                    'contenu' => $cr->getContenu(),
                    'valide' => $cr->isValide(),
                ] : null,
            ];
        }, $interventions));
    }

    #[Route('/comptes-rendus/{id}', name: 'compte_rendu_detail', methods: ['GET'])]
    public function detail(
        int $id,
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        $intervention = $interventionRepo->find($id);
        if (!$intervention || $intervention->getBeneficiaire() !== $beneficiaire) {
            return $this->json(['error' => 'Intervention non trouvée'], 404);
        }

        $cr = $intervention->getCompteRendu();
        if (!$cr) {
            return $this->json(['error' => 'Aucun compte rendu pour cette intervention'], 404);
        }

        return $this->json([
            'id' => $cr->getId(),
            'intervention' => [
                'id' => $intervention->getId(),
                'date' => $intervention->getDateDebut()?->format('d/m/Y'),
                'type' => $intervention->getTypeIntervention()?->value,
                'intervenant' => $intervention->getIntervenant()->getUtilisateur()->getNom(),
            ],
            'dateRedaction' => $cr->getDateRedaction()?->format('d/m/Y H:i'),
            'contenu' => $cr->getContenu(),
            'modeRedaction' => $cr->getModeRedaction()?->value,
            'valide' => $cr->isValide(),
        ]);
    }
}
