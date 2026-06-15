<?php

namespace App\DataFixtures;

use App\Entity\Beneficiaire;
use App\Entity\Intervenant;
use App\Entity\Intervention;
use App\Entity\Planning;
use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InterventionFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class, PlanningFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // [benRef, intervRef, planningRef, dayStr, hDeb, hFin, type, statut]
        $slots = [

            // ── Semaine dernière (planning_0 = Archive) ──
            ['ben_0', 'interv_0', 'planning_0', '2026-06-15', '08:00', '09:00', TypeIntervention::Toilette,       StatutIntervention::Terminee],
            ['ben_1', 'interv_1', 'planning_0', '2026-06-16', '09:00', '10:30', TypeIntervention::Menage,         StatutIntervention::Terminee],
            ['ben_2', 'interv_2', 'planning_0', '2026-06-17', '14:00', '15:00', TypeIntervention::Repas,          StatutIntervention::Terminee],
            ['ben_3', 'interv_0', 'planning_0', '2026-06-18', '17:00', '18:00', TypeIntervention::Accompagnement, StatutIntervention::Terminee],

            // ── Semaine courante (planning_1 = Publie) ──
            ['ben_0', 'interv_0', 'planning_1', '2026-06-22', '08:00', '09:00', TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            ['ben_1', 'interv_1', 'planning_1', '2026-06-22', '09:30', '11:00', TypeIntervention::Menage,         StatutIntervention::Planifiee],
            ['ben_2', 'interv_2', 'planning_1', '2026-06-22', '10:00', '11:00', TypeIntervention::Repas,          StatutIntervention::Planifiee],
            ['ben_3', 'interv_0', 'planning_1', '2026-06-22', '14:00', '15:30', TypeIntervention::Accompagnement, StatutIntervention::Planifiee],
            ['ben_0', 'interv_1', 'planning_1', '2026-06-23', '08:00', '09:00', TypeIntervention::Soins,          StatutIntervention::Planifiee],
            ['ben_2', 'interv_2', 'planning_1', '2026-06-23', '14:00', '15:00', TypeIntervention::Surveillance,   StatutIntervention::Planifiee],
            ['ben_1', 'interv_0', 'planning_1', '2026-06-24', '09:00', '10:30', TypeIntervention::Menage,         StatutIntervention::Planifiee],
            ['ben_3', 'interv_2', 'planning_1', '2026-06-24', '16:00', '17:00', TypeIntervention::Repas,          StatutIntervention::Planifiee],

            // ── Nouveaux bénéficiaires – lundi 22 juin ──
            ['ben_4',  'interv_0', 'planning_1', '2026-06-22', '07:00', '08:00', TypeIntervention::Toilette,       StatutIntervention::Terminee],
            ['ben_5',  'interv_3', 'planning_1', '2026-06-22', '08:30', '09:30', TypeIntervention::Menage,         StatutIntervention::Terminee],
            ['ben_6',  'interv_1', 'planning_1', '2026-06-22', '09:00', '10:00', TypeIntervention::Repas,          StatutIntervention::Terminee],
            ['ben_7',  'interv_3', 'planning_1', '2026-06-22', '10:00', '11:00', TypeIntervention::Soins,          StatutIntervention::Planifiee],
            ['ben_8',  'interv_2', 'planning_1', '2026-06-22', '11:00', '12:00', TypeIntervention::Surveillance,   StatutIntervention::Planifiee],
            ['ben_9',  'interv_0', 'planning_1', '2026-06-22', '11:30', '12:30', TypeIntervention::Accompagnement, StatutIntervention::Planifiee],

            // ── Admin2 — lundi 22 juin (planning_3) ──
            ['ben2_0', 'interv2_0', 'planning_3', '2026-06-22', '09:00', '10:00', TypeIntervention::Soins,          StatutIntervention::Planifiee],
            ['ben2_1', 'interv2_1', 'planning_3', '2026-06-22', '10:00', '11:00', TypeIntervention::Menage,         StatutIntervention::Planifiee],
            ['ben2_2', 'interv2_0', 'planning_3', '2026-06-22', '14:00', '15:00', TypeIntervention::Accompagnement, StatutIntervention::Planifiee],

            // ── Semaine prochaine (planning_2 = Brouillon) ──
            ['ben_0', 'interv_0', 'planning_2', '2026-06-29', '08:00', '09:00', TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            ['ben_1', 'interv_1', 'planning_2', '2026-06-30', '09:00', '10:30', TypeIntervention::Menage,         StatutIntervention::Planifiee],
        ];

        foreach ($slots as $k => [$benRef, $ivRef, $planRef, $dayStr, $hDeb, $hFin, $type, $statut]) {
            $date = new \DateTime($dayStr);

            $i = new Intervention();
            $i->setBeneficiaire($this->getReference($benRef, Beneficiaire::class));
            $i->setIntervenant($this->getReference($ivRef, Intervenant::class));
            $i->setPlanning($this->getReference($planRef, Planning::class));
            $i->setDateDebut(new \DateTime($date->format('Y-m-d') . ' ' . $hDeb));
            $i->setDateFin(new \DateTime($date->format('Y-m-d') . ' ' . $hFin));
            $i->setTypeIntervention($type);
            $i->setStatut($statut);
            $manager->persist($i);

            $this->addReference('intervention_' . $k, $i);
        }

        $manager->flush();
    }
}
