<?php

namespace App\DataFixtures;

use App\Entity\Administrateur;
use App\Entity\Beneficiaire;
use App\Entity\Intervenant;
use App\Entity\Utilisateur;
use App\Enum\NiveauAcces;
use App\Enum\RoleUtilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── Administrateur ──
        $adminUser = new Utilisateur();
        $adminUser->setNom('Admin WeCare');
        $adminUser->setEmail('admin@wecare.fr');
        $adminUser->setMdp($this->hasher->hashPassword($adminUser, 'admin123'));
        $adminUser->setRole(RoleUtilisateur::Administrateur);
        $manager->persist($adminUser);

        $admin = new Administrateur();
        $admin->setUtilisateur($adminUser);
        $admin->setNiveauAcces(NiveauAcces::SuperAdmin);
        $manager->persist($admin);

        $this->addReference('admin', $admin);

        // ── Intervenants (4) ──
        $intervenantsData = [
            ['Léo Lambert',     'leo@wecare.fr',    'Aide à domicile',   '0601020304', 'Renault Clio', 20.0, true,  '2024-09-01'],
            ['Marie Dumont',    'marie@wecare.fr',  'Soins infirmiers',  '0607080910', 'Peugeot 208',  15.0, true,  '2024-10-15'],
            ['Karim Benali',    'karim@wecare.fr',  'Auxiliaire de vie', '0611121314', null,            25.0, false, '2025-01-10'],
            ['Sophie Marchand', 'sophie@wecare.fr', 'Infirmière',        '0622334455', 'Citroën C3',   18.0, true,  '2025-03-01'],
        ];

        foreach ($intervenantsData as $k => [$nom, $email, $spe, $tel, $veh, $rayon, $dispo, $date]) {
            $u = new Utilisateur();
            $u->setNom($nom);
            $u->setEmail($email);
            $u->setMdp($this->hasher->hashPassword($u, 'interv123'));
            $u->setRole(RoleUtilisateur::Intervenant);
            $manager->persist($u);

            $iv = new Intervenant();
            $iv->setUtilisateur($u);
            $iv->setAdminCreateur($admin);
            $iv->setSpecialite($spe);
            $iv->setTelephone($tel);
            $iv->setDisponibilite($dispo);
            $iv->setRayonIntervention($rayon);
            $iv->setVehicule($veh);
            $iv->setCreatedAt(new \DateTimeImmutable($date));
            $manager->persist($iv);

            $this->addReference('interv_' . $k, $iv);
        }

        // ── Bénéficiaires (12) ──
        $beneficiairesData = [
            ['Simone Ruault',     'simone@mail.fr',      '1960-03-12', '3 allée des Roses, 33100 Bordeaux',         '44.8378', '-0.5792', 'modere',   'Alzheimer débutant'],
            ['Denise Huard',      'denise@mail.fr',      '1947-07-22', '9 rue des Cerises, 33000 Bordeaux',         '44.8340', '-0.5820', 'eleve',    'Diabète type 2'],
            ['Fatima Musson',     'fatima@mail.fr',      '1955-11-05', '21 rue Pasteur, 33600 Pessac',              '44.8050', '-0.6310', 'faible',   null],
            ['Émile Neveu',       'emile@mail.fr',       '1938-04-30', '23 rue des Vignes, 33100 Bordeaux',         '44.8200', '-0.5700', 'critique', 'Insuffisance cardiaque'],
            ['Robert Tissier',    'robert@mail.fr',      '1943-05-14', '7 rue Gambetta, 33000 Bordeaux',            '44.8420', '-0.5710', 'modere',   'BPCO'],
            ['Yvonne Cazenave',   'yvonne@mail.fr',      '1952-08-20', '14 avenue du Parc, 33200 Bordeaux',         '44.8490', '-0.5940', 'faible',   null],
            ['Jean-Pierre Allard','jp.allard@mail.fr',   '1935-12-01', '52 cours de la Marne, 33800 Bordeaux',      '44.8260', '-0.5810', 'critique', 'AVC séquellaire'],
            ['Marguerite Dumas',  'marguerite@mail.fr',  '1948-09-15', '3 allée de Tourny, 33000 Bordeaux',         '44.8415', '-0.5780', 'eleve',    'Parkinson débutant'],
            ['Henri Marchais',    'henri@mail.fr',       '1941-03-22', '8 rue Pasteur, 33400 Talence',              '44.8070', '-0.5870', 'modere',   'Diabète type 1'],
            ['Paulette Vidal',    'paulette@mail.fr',    '1957-11-30', '21 rue Nansouty, 33800 Bordeaux',           '44.8310', '-0.5660', 'faible',   null],
            ['Georges Faure',     'georges@mail.fr',     '1940-06-07', '5 avenue de Brienne, 33100 Bordeaux',       '44.8370', '-0.5890', 'eleve',    'Insuffisance rénale'],
            ['Madeleine Blanc',   'madeleine@mail.fr',   '1955-04-18', '12 rue Judaïque, 33000 Bordeaux',           '44.8450', '-0.5870', 'modere',   null],
        ];

        foreach ($beneficiairesData as $k => [$nom, $email, $dn, $adresse, $lat, $lng, $risque, $patho]) {
            $u = new Utilisateur();
            $u->setNom($nom);
            $u->setEmail($email);
            $u->setMdp($this->hasher->hashPassword($u, 'patient123'));
            $u->setRole(RoleUtilisateur::Beneficiaire);
            $manager->persist($u);

            $b = new Beneficiaire();
            $b->setUtilisateur($u);
            $b->setAdminCreateur($admin);
            $b->setDateNaissance(new \DateTime($dn));
            $b->setAdresse($adresse);
            $b->setLatitude($lat);
            $b->setLongitude($lng);
            $b->setNiveauRisque($risque);
            $b->setPathologie($patho);
            $manager->persist($b);

            $this->addReference('ben_' . $k, $b);
        }

        $manager->flush();
    }
}
