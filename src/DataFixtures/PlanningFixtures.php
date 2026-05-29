<?php

namespace App\DataFixtures;

use App\Entity\Administrateur;
use App\Entity\Intervenant;
use App\Entity\Planning;
use App\Enum\StatutPlanning;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlanningFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->getReference('admin', Administrateur::class);

        // [semaine, statut, intervenant refs inclus]
        $planningsData = [
            ['monday last week',  StatutPlanning::Archive,  ['interv_0', 'interv_2']],
            ['monday this week',  StatutPlanning::Publie,   ['interv_0', 'interv_1', 'interv_2', 'interv_3']],
            ['monday next week',  StatutPlanning::Brouillon, ['interv_0', 'interv_1']],
        ];

        foreach ($planningsData as $k => [$semaine, $statut, $ivRefs]) {
            $p = new Planning();
            $p->setAdmin($admin);
            $p->setSemaine(new \DateTime($semaine));
            $p->setStatut($statut);

            foreach ($ivRefs as $ref) {
                $p->addIntervenant($this->getReference($ref, Intervenant::class));
            }

            $manager->persist($p);
            $this->addReference('planning_' . $k, $p);
        }

        $manager->flush();
    }
}
