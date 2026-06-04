<?php

namespace App\Controller\Admin;

use App\Entity\Administrateur;
use App\Entity\Planning;
use App\Entity\Utilisateur;
use App\Enum\StatutPlanning;
use App\Repository\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;

trait AdminTrait
{
    protected const PALETTE      = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
    protected const JOURS        = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    protected const JOURS_COURTS = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    protected const MOIS         = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    protected const MOIS_COURTS  = ['', 'jan', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

    private function getCurrentAdmin(): ?Administrateur
    {
        $user = $this->getUser();
        if ($user instanceof Utilisateur) {
            return $user->getAdministrateur();
        }
        return null;
    }

    protected function getInitiales(string $nom, int $maxLen = 2): string
    {
        $parts = array_values(array_filter(explode(' ', trim($nom))));
        $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
        return mb_substr($init, 0, $maxLen);
    }

    protected function findOrCreatePlanning(\DateTime $date, PlanningRepository $planningRepo, EntityManagerInterface $em): Planning
    {
        $dow       = (int) $date->format('N');
        $weekStart = (clone $date)->modify('-' . ($dow - 1) . ' days midnight');
        $planning  = $planningRepo->findOneBy(['semaine' => $weekStart]);
        if (!$planning) {
            $planning = new Planning();
            $planning->setAdmin($this->getCurrentAdmin());
            $planning->setSemaine($weekStart);
            $planning->setStatut(StatutPlanning::Publie);
            $em->persist($planning);
        }
        return $planning;
    }
}
