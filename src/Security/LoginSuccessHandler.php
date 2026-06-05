<?php

namespace App\Security;

use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var Utilisateur $user */
        $user = $token->getUser();

        // JSON login (API) → retourner du JSON
        if ($request->getPathInfo() === '/api/login') {
            return new JsonResponse([
                'success' => true,
                'nom'     => $user->getNom(),
                'email'   => $user->getEmail(),
                'role'    => $user->getRole()->value,
            ]);
        }

        // Form login → rediriger selon le rôle
        $route = match ($user->getRole()) {
            RoleUtilisateur::Administrateur => 'admin_dashboard',
            RoleUtilisateur::Intervenant    => 'intervenant_dashboard',
            RoleUtilisateur::Beneficiaire   => 'beneficiaire_dashboard',
        };

        return new RedirectResponse($this->router->generate($route));
    }
}
