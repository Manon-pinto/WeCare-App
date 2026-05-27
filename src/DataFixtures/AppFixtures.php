<?php

namespace App\DataFixtures;

use App\Entity\Administrateur;
use App\Entity\Beneficiaire;
use App\Entity\CompteRendu;
use App\Entity\Intervenant;
use App\Entity\Intervention;
use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Entity\Planning;
use App\Enum\ModeRedaction;
use App\Enum\NiveauAcces;
use App\Enum\StatutPlanning;
use App\Enum\RoleUtilisateur;
use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use App\Enum\TypeNotification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── Admin ──
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
        $manager->flush();

        // ── Intervenant ──
        $intervUser = new Utilisateur();
        $intervUser->setNom('Léo Lambert');
        $intervUser->setEmail('leo@wecare.fr');
        $intervUser->setMdp($this->hasher->hashPassword($intervUser, 'leo123'));
        $intervUser->setRole(RoleUtilisateur::Intervenant);
        $manager->persist($intervUser);

        $interv = new Intervenant();
        $interv->setUtilisateur($intervUser);
        $interv->setAdminCreateur($admin);
        $interv->setSpecialite('Aide à domicile');
        $interv->setTelephone('0601020304');
        $interv->setDisponibilite(true);
        $interv->setRayonIntervention(20.0);
        $interv->setVehicule('Renault Clio');
        $interv->setCreatedAt(new \DateTimeImmutable('2024-09-01'));
        $manager->persist($interv);
        $manager->flush();

        // ── Bénéficiaires ──
        $patientsData = [
            ['Simone Ruault', 'simone@mail.fr', '3 allée des Roses, 33100 Bordeaux',  '44.8378', '-0.5792'],
            ['Denise Huard',  'denise@mail.fr', '9 rue des Cerises, 33000 Bordeaux',  '44.8340', '-0.5820'],
            ['Fatima Musson', 'fatima@mail.fr', '21 rue Pasteur, 33600 Pessac',       '44.8050', '-0.6310'],
            ['Émile Neveu',   'emile@mail.fr',  '23 rue des Vignes, 33100 Bordeaux',  '44.8200', '-0.5700'],
        ];

        $beneficiaires = [];
        foreach ($patientsData as [$nom, $email, $adresse, $lat, $lng]) {
            $u = new Utilisateur();
            $u->setNom($nom);
            $u->setEmail($email);
            $u->setMdp($this->hasher->hashPassword($u, 'patient123'));
            $u->setRole(RoleUtilisateur::Beneficiaire);
            $manager->persist($u);

            $b = new Beneficiaire();
            $b->setUtilisateur($u);
            $b->setAdminCreateur($admin);
            $b->setAdresse($adresse);
            $b->setDateNaissance(new \DateTime('1940-01-01'));
            $b->setLatitude($lat);
            $b->setLongitude($lng);
            $b->setNiveauRisque('modere');
            $manager->persist($b);
            $beneficiaires[] = $b;
        }
        $manager->flush();

        // ── Planning de la semaine ──
        $planning = new Planning();
        $planning->setAdmin($admin);
        $planning->setSemaine(new \DateTime('monday this week'));
        $planning->setStatut(StatutPlanning::Publie);
        $manager->persist($planning);
        $manager->flush();

        // ── Interventions ──
        $slots = [
            [$beneficiaires[0], '-1 day',  '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Terminee],
            [$beneficiaires[0], 'today',   '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[0], '+1 day',  '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[0], '+2 days', '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[0], '+3 days', '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[1], '-1 day',  '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Terminee],
            [$beneficiaires[1], 'today',   '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Planifiee],
            [$beneficiaires[1], '+1 day',  '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Planifiee],
            [$beneficiaires[1], '+3 days', '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Planifiee],
            [$beneficiaires[2], '-2 days', '14:00', '15:00',  TypeIntervention::Repas,          StatutIntervention::Terminee],
            [$beneficiaires[2], 'today',   '14:00', '15:00',  TypeIntervention::Repas,          StatutIntervention::Planifiee],
            [$beneficiaires[2], '+2 days', '14:00', '15:00',  TypeIntervention::Repas,          StatutIntervention::Planifiee],
            [$beneficiaires[3], 'today',   '17:00', '18:00',  TypeIntervention::Accompagnement, StatutIntervention::Planifiee],
            [$beneficiaires[3], '+2 days', '17:00', '18:00',  TypeIntervention::Accompagnement, StatutIntervention::Planifiee],
        ];

        $interventions = [];
        foreach ($slots as [$ben, $dayStr, $hDeb, $hFin, $type, $statut]) {
            $date = new \DateTime($dayStr);
            $i = new Intervention();
            $i->setBeneficiaire($ben);
            $i->setIntervenant($interv);
            $i->setDateDebut(new \DateTime($date->format('Y-m-d') . ' ' . $hDeb));
            $i->setDateFin(new \DateTime($date->format('Y-m-d') . ' ' . $hFin));
            $i->setTypeIntervention($type);
            $i->setStatut($statut);
            $i->setPlanning($planning);
            $manager->persist($i);
            $interventions[] = ['entity' => $i, 'statut' => $statut];
        }
        $manager->flush();

        // ── Comptes rendus (interventions terminées) ──
        $crTexts = [
            'Simone bien réveillée. Toilette effectuée sans difficulté. Médicaments pris. RAS.',
            'Courses effectuées selon la liste. Denise de bonne humeur. A demandé des yaourts pour la prochaine fois. RAS par ailleurs.',
            'Fatima fatiguée ce matin. Repas préparé. Légère tristesse mentionnée.',
        ];

        $idx = 0;
        foreach ($interventions as $slot) {
            if ($slot['statut'] !== StatutIntervention::Terminee) continue;
            if (!isset($crTexts[$idx])) break;
            $cr = new CompteRendu();
            $cr->setIntervention($slot['entity']);
            $cr->setContenu($crTexts[$idx]);
            $cr->setModeRedaction(ModeRedaction::Manuel);
            $cr->setDateRedaction(new \DateTime($slot['entity']->getDateDebut()->format('Y-m-d') . ' 20:00'));
            $cr->setValide(true);
            $manager->persist($cr);
            $idx++;
        }

        // ── Notifications ──
        $notifData = [
            [TypeNotification::Rappel, 'Visite prévue demain matin avec Simone Ruault — pensez à apporter le matériel de toilette.', false, '-1 day'],
            [TypeNotification::IncidentSignale, 'Un incident a été signalé pour Denise Huard et est en attente de traitement par le coordinateur.', false, '-2 days'],
            [TypeNotification::CompteRenduValide, 'Votre compte rendu pour Fatima Musson du 24/05 a été validé par le coordinateur.', true, '-3 days'],
        ];

        foreach ($notifData as [$type, $msg, $lu, $ago]) {
            $n = new Notification();
            $n->setUtilisateur($intervUser);
            $n->setType($type);
            $n->setLu($lu);
            $n->setMessage($msg);
            $n->setCreatedAt(new \DateTimeImmutable($ago));
            $manager->persist($n);
        }

        $manager->flush();
    }
}
