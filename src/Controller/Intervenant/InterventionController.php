<?php

namespace App\Controller\Intervenant;

use App\Enum\StatutIntervention;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class InterventionController extends AbstractController
{
    #[Route('/interventions/{id}/terminer', name: 'intervention_terminer', methods: ['PATCH'])]
    public function terminer(
        int $id,
        InterventionRepository $interventionRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        $intervention = $interventionRepo->find($id);
        if (!$intervention || $intervention->getIntervenant() !== $intervenant) {
            return $this->json(['error' => 'Intervention non trouvée'], 404);
        }

        $intervention->setStatut(StatutIntervention::Terminee);
        $em->flush();

        return $this->json(['message' => 'Intervention terminée']);
    }
}
