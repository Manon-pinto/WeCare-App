<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $token->getUser();
        return new JsonResponse([
            'success' => true,
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
        ]);
    }
}
