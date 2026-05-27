<?php

namespace App\Controller\Intervenant;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/intervenant', name: 'api_intervenant_')]
class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function profil(): JsonResponse
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        return $this->json([
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
            'specialite' => $intervenant->getSpecialite(),
            'telephone' => $intervenant->getTelephone(),
            'disponibilite' => $intervenant->isDisponibilite(),
            'rayonIntervention' => $intervenant->getRayonIntervention(),
            'vehicule' => $intervenant->getVehicule(),
            'membreDepuis' => $intervenant->getCreatedAt()?->format('d/m/Y'),
        ]);
    }

    #[Route('/profil', name: 'profil_modifier', methods: ['PUT'])]
    public function modifier(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['motDePasse']) && !empty($data['motDePasse'])) {
            $hashed = $passwordHasher->hashPassword($user, $data['motDePasse']);
            $user->setMdp($hashed);
        }
        if (isset($data['telephone'])) {
            $intervenant->setTelephone($data['telephone']);
        }
        if (isset($data['specialite'])) {
            $intervenant->setSpecialite($data['specialite']);
        }
        if (isset($data['disponibilite'])) {
            $intervenant->setDisponibilite((bool) $data['disponibilite']);
        }
        if (isset($data['rayonIntervention'])) {
            $intervenant->setRayonIntervention((float) $data['rayonIntervention']);
        }
        if (isset($data['vehicule'])) {
            $intervenant->setVehicule($data['vehicule'] ?: null);
        }

        $em->flush();

        return $this->json(['message' => 'Profil mis à jour avec succès']);
    }
}
