<?php

namespace App\DataFixtures;

use App\Entity\Entreprise;
use App\Enum\StatutEntreprise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EntrepriseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $entreprisesData = [
            [
                'nom'             => 'Auxiliaire Services Bordeaux',
                'email'           => 'contact@asb-bordeaux.fr',
                'siret'           => '12345678901234',
                'telephone'       => '0556112233',
                'adresse'         => '15 cours du Chapeau Rouge',
                'dateInscription' => '2026-01-15',
                'statut'          => StatutEntreprise::Active,
            ],
            [
                'nom'             => 'Centre Soins Aquitaine',
                'email'           => 'admin@csa-aquitaine.fr',
                'siret'           => '98765432109876',
                'telephone'       => '0556445566',
                'adresse'         => '8 rue Judaïque, 33000 Bordeaux',
                'dateInscription' => '2026-03-10',
                'statut'          => StatutEntreprise::Active,
            ],
        ];

        foreach ($entreprisesData as $k => $data) {
            $e = new Entreprise();
            $e->setNom($data['nom']);
            $e->setEmail($data['email']);
            $e->setSiret($data['siret']);
            $e->setTelephone($data['telephone']);
            $e->setAdresse($data['adresse']);
            $e->setDateInscription(new \DateTime($data['dateInscription']));
            $e->setStatut($data['statut']);
            $manager->persist($e);

            $this->addReference('entreprise_' . $k, $e);
        }

        $manager->flush();
    }
}
