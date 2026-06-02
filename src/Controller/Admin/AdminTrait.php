<?php

namespace App\Controller\Admin;

use App\Entity\Administrateur;
use App\Entity\Utilisateur;

trait AdminTrait
{
    private function getCurrentAdmin(): ?Administrateur
    {
        $user = $this->getUser();
        if ($user instanceof Utilisateur) {
            return $user->getAdministrateur();
        }
        return null;
    }

    /** Retourne les IDs des intervenants de l'admin courant (tableau vide = aucun filtre) */
    private function getAdminIvIds(array $ivData): array
    {
        return array_keys($ivData);
    }
}
