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

        // ── Aidants admin2 ──
        $aidantsAdmin2 = [
            ['beneficiaire' => 'ben2_0', 'nom' => 'Blanc', 'prenom' => 'Sophie',  'telephone' => '0677889900', 'email' => 'sophie.blanc@mail.fr', 'lien' => 'Fille'],
            ['beneficiaire' => 'ben2_2', 'nom' => 'Petit', 'prenom' => 'Bernard', 'telephone' => '0688990011', 'email' => 'bernard.petit@mail.fr', 'lien' => 'Fils'],
        ];

        foreach ($aidantsAdmin2 as $data) {
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
