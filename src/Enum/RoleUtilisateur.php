<?php

namespace App\Enum;

enum RoleUtilisateur: string
{
    case Administrateur = 'administrateur';
    case Intervenant = 'intervenant';
    case Beneficiaire = 'beneficiaire';
}
