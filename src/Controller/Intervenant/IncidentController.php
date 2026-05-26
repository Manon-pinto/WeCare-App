<?php

namespace App\Controller\Intervenant;

use App\Entity\Incident;
use App\Enum\GraviteIncident;
use App\Enum\StatutIncident;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class IncidentController extends AbstractController
{
    #[Route('/interventions/{id}/incident', name: 'incident_signaler', methods: ['POST'])]
    public function signaler(
        int $id,
        Request $request,
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

        $data = json_decode($request->getContent(), true);

        if (empty($data['description'])) {
            return $this->json(['error' => 'La description est obligatoire'], 422);
        }

        $incident = new Incident();
        $incident->setIntervention($intervention);
        $incident->setDescription($data['description']);
        $incident->setDateSignalement(new \DateTime());
        $incident->setGravite(GraviteIncident::from($data['gravite'] ?? GraviteIncident::Moderee->value));
        $incident->setStatut(StatutIncident::Signale);
        $incident->setZone($data['zone'] ?? null);

        $em->persist($incident);
        $em->flush();

        return $this->json([
            'id' => $incident->getId(),
            'message' => 'Incident signalé avec succès',
        ], 201);
    }

    #[Route('/incidents', name: 'incidents_liste', methods: ['GET'])]
    public function liste(
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $incidents = $interventionRepo->findIncidentsByIntervenant($intervenant);

        return $this->json(array_map(function ($incident) {
            $i = $incident->getIntervention();
            return [
                'id' => $incident->getId(),
                'description' => $incident->getDescription(),
                'gravite' => $incident->getGravite()?->value,
                'statut' => $incident->getStatut()?->value,
                'dateSignalement' => $incident->getDateSignalement()?->format('d/m/Y H:i'),
                'zone' => $incident->getZone(),
                'intervention' => [
                    'id' => $i->getId(),
                    'beneficiaire' => $i->getBeneficiaire()->getUtilisateur()->getNom(),
                    'date' => $i->getDateDebut()?->format('d/m/Y'),
                ],
            ];
        }, $incidents));
    }
}
