<?php

namespace App\DataFixtures;

use App\Entity\Abonnement;
use App\Entity\Entreprise;
use App\Entity\PlanTarifaire;
use App\Enum\StatutAbonnement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AbonnementFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [EntrepriseFixtures::class, PlanTarifaireFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $abonnementsData = [
            [
                'entreprise'   => 'entreprise_0',  // Auxiliaire Services Bordeaux
                'plan'         => 'plan_0',         // Starter (59€, 1-50 bénéf.)
                'dateDebut'    => '2026-01-15',
                'statut'       => StatutAbonnement::Actif,
            ],
            [
                'entreprise'   => 'entreprise_1',  // Centre Soins Aquitaine
                'plan'         => 'plan_2',         // Standard (129€, 101-150 bénéf.)
                'dateDebut'    => '2026-03-10',
                'statut'       => StatutAbonnement::Actif,
            ],
        ];

        foreach ($abonnementsData as $data) {
            $a = new Abonnement();
            $a->setEntreprise($this->getReference($data['entreprise'], Entreprise::class));
            $a->setPlanTarifaire($this->getReference($data['plan'], PlanTarifaire::class));
            $a->setDateDebut(new \DateTime($data['dateDebut']));
            $a->setStatut($data['statut']);
            $manager->persist($a);
        }

        $manager->flush();
    }
}
