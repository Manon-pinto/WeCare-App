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
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
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
        if (isset($data['adresse'])) {
            $beneficiaire->setAdresse($data['adresse']);
        }
        if (isset($data['aidant'])) {
            $beneficiaire->setAidant($data['aidant']);
        }

        $em->flush();

        return $this->json(['message' => 'Compte mis à jour avec succès']);
    }
}
