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
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        if (!$intervenant) {
            return $this->json(['error' => 'Intervenant non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $user->setNom(trim($data['nom']));
        }
        if (isset($data['email'])) {
            $user->setEmail(trim($data['email']));
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

    #[Route('/changer-mdp', name: 'changer_mdp', methods: ['POST'])]
    public function changerMotDePasse(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $ancienMdp = $data['ancienMotDePasse'] ?? '';
        $nouveauMdp = $data['nouveauMotDePasse'] ?? '';
        $confirmMdp = $data['confirmationMotDePasse'] ?? '';

        if (!$passwordHasher->isPasswordValid($user, $ancienMdp)) {
            return $this->json(['error' => 'Mot de passe actuel incorrect.'], 400);
        }

        if ($nouveauMdp !== $confirmMdp) {
            return $this->json(['error' => 'Les nouveaux mots de passe ne correspondent pas.'], 400);
        }

        $erreurAnssi = $this->validerCriteresAnssi($nouveauMdp);
        if ($erreurAnssi) {
            return $this->json(['error' => $erreurAnssi], 400);
        }

        $user->setMdp($passwordHasher->hashPassword($user, $nouveauMdp));
        $em->flush();

        return $this->json(['message' => 'Mot de passe modifié avec succès.']);
    }

    private function validerCriteresAnssi(string $mdp): ?string
    {
        if (strlen($mdp) < 12) {
            return 'Le mot de passe doit contenir au moins 12 caractères.';
        }
        if (!preg_match('/[A-Z]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins une lettre majuscule.';
        }
        if (!preg_match('/[a-z]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins une lettre minuscule.';
        }
        if (!preg_match('/[0-9]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!preg_match('/[\W_]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins un caractère spécial.';
        }
        return null;
    }
}
