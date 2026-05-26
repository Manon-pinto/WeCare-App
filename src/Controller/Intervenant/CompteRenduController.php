<?php

namespace App\Controller\Intervenant;

use App\Entity\CompteRendu;
use App\Enum\ModeRedaction;
use App\Repository\CompteRenduRepository;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class CompteRenduController extends AbstractController
{
    #[Route('/comptes-rendus', name: 'comptes_rendus_liste', methods: ['GET'])]
    public function liste(
        InterventionRepository $interventionRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $interventions = $interventionRepo->findWithCompteRenduByIntervenant($intervenant);

        return $this->json(array_map(function ($i) {
            $cr = $i->getCompteRendu();
            return [
                'interventionId' => $i->getId(),
                'beneficiaire' => $i->getBeneficiaire()->getUtilisateur()->getNom(),
                'date' => $i->getDateDebut()?->format('d/m/Y'),
                'type' => $i->getTypeIntervention()?->value,
                'compteRendu' => $cr ? [
                    'id' => $cr->getId(),
                    'dateRedaction' => $cr->getDateRedaction()?->format('d/m/Y H:i'),
                    'contenu' => $cr->getContenu(),
                    'modeRedaction' => $cr->getModeRedaction()?->value,
                    'valide' => $cr->isValide(),
                ] : null,
            ];
        }, $interventions));
    }

    #[Route('/interventions/{id}/compte-rendu', name: 'compte_rendu_creer', methods: ['POST'])]
    public function creer(
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

        if ($intervention->getCompteRendu()) {
            return $this->json(['error' => 'Un compte rendu existe déjà pour cette intervention'], 409);
        }

        $data = json_decode($request->getContent(), true);

        $cr = new CompteRendu();
        $cr->setIntervention($intervention);
        $cr->setContenu($data['contenu'] ?? '');
        $cr->setDateRedaction(new \DateTime());
        $cr->setModeRedaction(ModeRedaction::from($data['modeRedaction'] ?? ModeRedaction::Manuel->value));
        $cr->setValide(false);

        $em->persist($cr);
        $em->flush();

        return $this->json([
            'id' => $cr->getId(),
            'message' => 'Compte rendu créé avec succès',
        ], 201);
    }

    #[Route('/comptes-rendus/{id}', name: 'compte_rendu_modifier', methods: ['PUT'])]
    public function modifier(
        int $id,
        Request $request,
        CompteRenduRepository $crRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        $cr = $crRepo->find($id);
        if (!$cr || $cr->getIntervention()->getIntervenant() !== $intervenant) {
            return $this->json(['error' => 'Compte rendu non trouvé'], 404);
        }

        if ($cr->isValide()) {
            return $this->json(['error' => 'Impossible de modifier un compte rendu validé'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['contenu'])) {
            $cr->setContenu($data['contenu']);
        }
        if (isset($data['modeRedaction'])) {
            $cr->setModeRedaction(ModeRedaction::from($data['modeRedaction']));
        }

        $em->flush();

        return $this->json(['message' => 'Compte rendu mis à jour']);
    }
}
