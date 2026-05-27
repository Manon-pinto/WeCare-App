<?php

namespace App\Controller\Beneficiaire;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class MonCompteController extends AbstractController
{
    #[Route('/mon-compte', name: 'mon_compte', methods: ['GET'])]
    public function monCompte(): JsonResponse
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $aidants = [];
        if ($beneficiaire->getAidant()) {
            $aidants[] = ['nom' => $beneficiaire->getAidant()];
        }

        return $this->json([
            'informationsPersonnelles' => [
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'dateNaissance' => $beneficiaire->getDateNaissance()?->format('d/m/Y'),
                'adresse' => $beneficiaire->getAdresse(),
                'latitude' => $beneficiaire->getLatitude(),
                'longitude' => $beneficiaire->getLongitude(),
            ],
            'informationsMedicales' => [
                'pathologie' => $beneficiaire->getPathologie(),
                'niveauRisque' => $beneficiaire->getNiveauRisque(),
            ],
            'aidants' => $aidants,
        ]);
    }

    #[Route('/mon-compte', name: 'mon_compte_modifier', methods: ['PUT'])]
    public function modifier(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $user->setNom(trim($data['nom']));
        }
        if (isset($data['email'])) {
            $user->setEmail(trim($data['email']));
        }
        if (isset($data['adresse'])) {
            $beneficiaire->setAdresse($data['adresse']);
        }

        $em->flush();

        return $this->json(['message' => 'Compte mis à jour avec succès']);
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
