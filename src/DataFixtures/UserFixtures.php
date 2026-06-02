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
            ['Léo Lambert',     'leo@wecare.fr',    'Aide à domicile',   '0601020304', 'Renault Clio', 20.0, 'actif',      '2024-09-01'],
            ['Marie Dumont',    'marie@wecare.fr',  'Soins infirmiers',  '0607080910', 'Peugeot 208',  15.0, 'actif',      '2024-10-15'],
            ['Karim Benali',    'karim@wecare.fr',  'Auxiliaire de vie', '0611121314', null,            25.0, 'conge',      '2025-01-10'],
            ['Sophie Marchand', 'sophie@wecare.fr', 'Infirmière',        '0622334455', 'Citroën C3',   18.0, 'actif',      '2025-03-01'],
        ];

        foreach ($intervenantsData as $k => [$nom, $email, $spe, $tel, $veh, $rayon, $statut, $date]) {
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
            $iv->setStatut($statut);
            $iv->setRayonIntervention($rayon);
            $iv->setVehicule($veh);
            $iv->setCreatedAt(new \DateTimeImmutable($date));
            $manager->persist($iv);

            $this->addReference('interv_' . $k, $iv);
        }

        // ── Bénéficiaires (12) ──
        $beneficiairesData = [
            ['Simone Ruault',     'simone@mail.fr',      '1960-03-12', '3 allée des Roses, 33100 Bordeaux',         '44.8378', '-0.5792', 'modere',   'Alzheimer débutant',    3],
            ['Denise Huard',      'denise@mail.fr',      '1947-07-22', '9 rue des Cerises, 33000 Bordeaux',         '44.8340', '-0.5820', 'eleve',    'Diabète type 2',        5],
            ['Fatima Musson',     'fatima@mail.fr',      '1955-11-05', '21 rue Pasteur, 33600 Pessac',              '44.8050', '-0.6310', 'faible',   null,                    2],
            ['Émile Neveu',       'emile@mail.fr',       '1938-04-30', '23 rue des Vignes, 33100 Bordeaux',         '44.8200', '-0.5700', 'critique', 'Insuffisance cardiaque', 7],
            ['Robert Tissier',    'robert@mail.fr',      '1943-05-14', '7 rue Gambetta, 33000 Bordeaux',            '44.8420', '-0.5710', 'modere',   'BPCO',                  3],
            ['Yvonne Cazenave',   'yvonne@mail.fr',      '1952-08-20', '14 avenue du Parc, 33200 Bordeaux',         '44.8490', '-0.5940', 'faible',   null,                    2],
            ['Jean-Pierre Allard','jp.allard@mail.fr',   '1935-12-01', '52 cours de la Marne, 33800 Bordeaux',      '44.8260', '-0.5810', 'critique', 'AVC séquellaire',       7],
            ['Marguerite Dumas',  'marguerite@mail.fr',  '1948-09-15', '3 allée de Tourny, 33000 Bordeaux',         '44.8415', '-0.5780', 'eleve',    'Parkinson débutant',    5],
            ['Henri Marchais',    'henri@mail.fr',       '1941-03-22', '8 rue Pasteur, 33400 Talence',              '44.8070', '-0.5870', 'modere',   'Diabète type 1',        3],
            ['Paulette Vidal',    'paulette@mail.fr',    '1957-11-30', '21 rue Nansouty, 33800 Bordeaux',           '44.8310', '-0.5660', 'faible',   null,                    1],
            ['Georges Faure',     'georges@mail.fr',     '1940-06-07', '5 avenue de Brienne, 33100 Bordeaux',       '44.8370', '-0.5890', 'eleve',    'Insuffisance rénale',   5],
            ['Madeleine Blanc',   'madeleine@mail.fr',   '1955-04-18', '12 rue Judaïque, 33000 Bordeaux',           '44.8450', '-0.5870', 'modere',   null,                    3],
        ];

        foreach ($beneficiairesData as $k => [$nom, $email, $dn, $adresse, $lat, $lng, $risque, $patho, $rdv]) {
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
            $b->setRdvParSemaine($rdv);
            $manager->persist($b);

            $this->addReference('ben_' . $k, $b);
        }

        // ═══════════════════════════════════════════════
        // ══  ENTREPRISE 2 — WeCare Lyon              ══
        // ═══════════════════════════════════════════════

        $admin2User = new Utilisateur();
        $admin2User->setNom('Admin WeCare Lyon');
        $admin2User->setEmail('admin2@wecare.fr');
        $admin2User->setMdp($this->hasher->hashPassword($admin2User, 'admin456'));
        $admin2User->setRole(RoleUtilisateur::Administrateur);
        $manager->persist($admin2User);

        $admin2 = new Administrateur();
        $admin2->setUtilisateur($admin2User);
        $admin2->setNiveauAcces(NiveauAcces::SuperAdmin);
        $manager->persist($admin2);
        $this->addReference('admin2', $admin2);

        // ── Intervenants admin2 (2) ──
        $intervenantsAdmin2 = [
            ['Antoine Leroy',  'antoine@wecare.fr', 'Infirmier(e)',    '0633445566', null,          15.0, 'actif', '2025-06-01'],
            ['Julie Martin',   'julie@wecare.fr',   'Aide-soignant(e)','0644556677', 'Ford Focus',   12.0, 'actif', '2025-07-01'],
        ];

        foreach ($intervenantsAdmin2 as $k => [$nom, $email, $spe, $tel, $veh, $rayon, $statut, $date]) {
            $u = new Utilisateur();
            $u->setNom($nom);
            $u->setEmail($email);
            $u->setMdp($this->hasher->hashPassword($u, 'interv123'));
            $u->setRole(RoleUtilisateur::Intervenant);
            $manager->persist($u);

            $iv = new Intervenant();
            $iv->setUtilisateur($u);
            $iv->setAdminCreateur($admin2);
            $iv->setSpecialite($spe);
            $iv->setTelephone($tel);
            $iv->setStatut($statut);
            $iv->setRayonIntervention($rayon);
            $iv->setVehicule($veh);
            $iv->setCreatedAt(new \DateTimeImmutable($date));
            $manager->persist($iv);
            $this->addReference('interv2_' . $k, $iv);
        }

        // ── Bénéficiaires admin2 (3) ──
        $beneficiairesAdmin2 = [
            ['Pierre Blanc',   'pierre@mail.fr',  '1950-03-15', '5 rue de la Croix, 69001 Lyon',       '45.7578', '4.8320', 'modere',   'Arthrose'],
            ['Lucie Bernard',  'lucie2@mail.fr',  '1962-08-20', '12 avenue Jean Jaurès, 69007 Lyon',   '45.7396', '4.8327', 'faible',   null],
            ['Roger Petit',    'roger@mail.fr',   '1945-11-10', '3 rue Mercière, 69002 Lyon',           '45.7640', '4.8340', 'eleve',    'Diabète type 2'],
        ];

        foreach ($beneficiairesAdmin2 as $k => [$nom, $email, $dn, $adresse, $lat, $lng, $risque, $patho]) {
            $u = new Utilisateur();
            $u->setNom($nom);
            $u->setEmail($email);
            $u->setMdp($this->hasher->hashPassword($u, 'patient123'));
            $u->setRole(RoleUtilisateur::Beneficiaire);
            $manager->persist($u);

            $b = new Beneficiaire();
            $b->setUtilisateur($u);
            $b->setAdminCreateur($admin2);
            $b->setDateNaissance(new \DateTime($dn));
            $b->setAdresse($adresse);
            $b->setLatitude($lat);
            $b->setLongitude($lng);
            $b->setNiveauRisque($risque);
            $b->setPathologie($patho);
            $manager->persist($b);
            $this->addReference('ben2_' . $k, $b);
        }

        $manager->flush();
    }
}
