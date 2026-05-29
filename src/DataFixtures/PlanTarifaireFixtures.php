<?php

namespace App\DataFixtures;

use App\Entity\PlanTarifaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlanTarifaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Plans basés sur la grille tarifaire WeCare (prix mensuel TTC)
        $plans = [
            ['Starter',    '59.00',  '1 à 50 bénéficiaires',     '2024-01-01', '2026-12-31'],
            ['Basic',      '89.00',  '51 à 100 bénéficiaires',   '2024-01-01', '2026-12-31'],
            ['Standard',   '129.00', '101 à 150 bénéficiaires',  '2024-01-01', '2026-12-31'],
            ['Pro',        '169.00', '151 à 200 bénéficiaires',  '2024-01-01', '2026-12-31'],
            ['Business',   '229.00', '201 à 300 bénéficiaires',  '2024-01-01', '2026-12-31'],
            ['Enterprise', '309.00', '301 à 500 bénéficiaires',  '2024-01-01', '2026-12-31'],
        ];

        foreach ($plans as $k => [$nom, $prix, $fonctionnalites, $debut, $fin]) {
            $plan = new PlanTarifaire();
            $plan->setNom($nom);
            $plan->setPrix($prix);
            $plan->setFonctionnalites($fonctionnalites);
            $plan->setDateDebut(new \DateTime($debut));
            $plan->setDateFin(new \DateTime($fin));
            $manager->persist($plan);

            $this->addReference('plan_' . $k, $plan);
        }

        $manager->flush();
    }
}
