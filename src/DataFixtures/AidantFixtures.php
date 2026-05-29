<?php

namespace App\DataFixtures;

use App\Entity\Aidant;
use App\Entity\Beneficiaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AidantFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $aidantsData = [
            [
                'beneficiaire' => 'ben_0',  // Simone Ruault
                'nom'          => 'Ruault',
                'prenom'       => 'Claire',
                'telephone'    => '0612345678',
                'email'        => 'claire.ruault@mail.fr',
                'lien'         => 'Fille',
            ],
            [
                'beneficiaire' => 'ben_3',  // Émile Neveu
                'nom'          => 'Neveu',
                'prenom'       => 'Thomas',
                'telephone'    => '0698765432',
                'email'        => 'thomas.neveu@mail.fr',
                'lien'         => 'Fils',
            ],
        ];

        foreach ($aidantsData as $data) {
            $a = new Aidant();
            $a->setBeneficiaire($this->getReference($data['beneficiaire'], Beneficiaire::class));
            $a->setNom($data['nom']);
            $a->setPrenom($data['prenom']);
            $a->setTelephone($data['telephone']);
            $a->setEmail($data['email']);
            $a->setLien($data['lien']);
            $manager->persist($a);
        }

        $manager->flush();
    }
}
