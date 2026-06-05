<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        if ($this->getUser()) {
            return $this->redirectAfterLogin();
        }
        return $this->render('login.html.twig');
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(): Response
    {
        if ($this->getUser()) {
            return $this->redirectAfterLogin();
        }
        return $this->render('register.html.twig');
    }

    private function redirectAfterLogin(): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_BENEFICIAIRE')) {
            return $this->redirectToRoute('beneficiaire_dashboard');
        }
        if ($this->isGranted('ROLE_ADMINISTRATEUR')) {
            return $this->redirectToRoute('admin_dashboard');
        }
        return $this->redirectToRoute('intervenant_dashboard');
    }

    #[Route('/api/inscription', name: 'api_inscription', methods: ['POST'])]
    public function apiInscription(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $nom = trim($data['nom'] ?? '');
        $email = trim($data['email'] ?? '');
        $mdp = $data['motDePasse'] ?? '';

        if (!$nom || !$email || !$mdp) {
            return $this->json(['error' => 'Nom, email et mot de passe requis.'], 400);
        }

        $existing = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if ($existing) {
            return $this->json(['error' => 'Cet email est déjà utilisé.'], 409);
        }

        $user = new Utilisateur();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setMdp($hasher->hashPassword($user, $mdp));
        $user->setRole(RoleUtilisateur::Intervenant);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Compte créé avec succès.'], 201);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(): never
    {
        throw new \LogicException('This should be intercepted by the security firewall.');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method should not be reached.');
    }
}
