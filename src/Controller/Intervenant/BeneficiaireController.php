<?php

namespace App\Controller\Intervenant;

use App\Repository\BeneficiaireRepository;
use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class BeneficiaireController extends AbstractController
{
    #[Route('/patients', name: 'patients_liste', methods: ['GET'])]
    public function liste(
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $beneficiaires = $interventionRepo->findDistinctBeneficiairesByIntervenant($intervenant);

        return $this->json(array_map(
            fn($b) => $this->serializeBeneficiaire($b),
            $beneficiaires
        ));
    }

    #[Route('/patients/{id}', name: 'patient_detail', methods: ['GET'])]
    public function detail(
        int $id,
        BeneficiaireRepository $beneficiaireRepo,
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $beneficiaire = $beneficiaireRepo->find($id);
        if (!$beneficiaire) {
            return $this->json(['error' => 'Patient non trouvé'], 404);
        }

        $prochaines = $interventionRepo->findUpcomingByIntervenantAndBeneficiaire($intervenant, $beneficiaire, 3);
        $passees = $interventionRepo->findPastByIntervenantAndBeneficiaire($intervenant, $beneficiaire, 5);

        return $this->json([
            'beneficiaire' => $this->serializeBeneficiaire($beneficiaire),
            'prochainesInterventions' => array_map(
                fn($i) => $this->serializeIntervention($i),
                $prochaines
            ),
            'historiqueInterventions' => array_map(
                fn($i) => $this->serializeIntervention($i),
                $passees
            ),
        ]);
    }

    private function serializeBeneficiaire(\App\Entity\Beneficiaire $b): array
    {
        $u = $b->getUtilisateur();
        return [
            'id' => $b->getId(),
            'nom' => $u->getNom(),
            'email' => $u->getEmail(),
            'adresse' => $b->getAdresse(),
            'telephone' => null,
            'dateNaissance' => $b->getDateNaissance()?->format('d/m/Y'),
            'pathologie' => $b->getPathologie(),
            'aidant' => $b->getAidant(),
            'niveauRisque' => $b->getNiveauRisque(),
            'latitude' => $b->getLatitude(),
            'longitude' => $b->getLongitude(),
        ];
    }

    private function serializeIntervention(\App\Entity\Intervention $i): array
    {
        return [
            'id' => $i->getId(),
            'dateDebut' => $i->getDateDebut()?->format('d/m/Y H:i'),
            'dateFin' => $i->getDateFin()?->format('H:i'),
            'type' => $i->getTypeIntervention()?->value,
            'statut' => $i->getStatut()?->value,
            'observations' => $i->getObservations(),
            'compteRendu' => $i->getCompteRendu() ? [
                'id' => $i->getCompteRendu()->getId(),
                'valide' => $i->getCompteRendu()->isValide(),
            ] : null,
        ];
    }
}
