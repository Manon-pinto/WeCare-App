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
        $adminUser->setMdp($this->hasher->hashPassword($adminUser, 'Admin1234!'));
        $adminUser->setRole(RoleUtilisateur::Administrateur);
        $manager->persist($adminUser);

        $admin = new Administrateur();
        $admin->setUtilisateur($adminUser);
        $admin->setNiveauAcces(NiveauAcces::SuperAdmin);
        $manager->persist($admin);
        $manager->flush();

        // ── Intervenants ──
        $intervenants = [];

        $u1 = new Utilisateur();
        $u1->setNom('Léo Lambert');
        $u1->setEmail('leo@wecare.fr');
        $u1->setMdp($this->hasher->hashPassword($u1, 'Leo1234!'));
        $u1->setRole(RoleUtilisateur::Intervenant);
        $manager->persist($u1);

        $interv1 = new Intervenant();
        $interv1->setUtilisateur($u1);
        $interv1->setAdminCreateur($admin);
        $interv1->setSpecialite('Aide à domicile');
        $interv1->setTelephone('0601020304');
        $interv1->setDisponibilite(true);
        $interv1->setRayonIntervention(20.0);
        $interv1->setVehicule('Renault Clio');
        $interv1->setCreatedAt(new \DateTimeImmutable('2024-09-01'));
        $manager->persist($interv1);
        $intervenants[] = $interv1;

        $u2 = new Utilisateur();
        $u2->setNom('Sophie Martin');
        $u2->setEmail('sophie@wecare.fr');
        $u2->setMdp($this->hasher->hashPassword($u2, 'Sophie1234!'));
        $u2->setRole(RoleUtilisateur::Intervenant);
        $manager->persist($u2);

        $interv2 = new Intervenant();
        $interv2->setUtilisateur($u2);
        $interv2->setAdminCreateur($admin);
        $interv2->setSpecialite('Aide-soignante');
        $interv2->setTelephone('0612345678');
        $interv2->setDisponibilite(true);
        $interv2->setRayonIntervention(15.0);
        $interv2->setVehicule('Peugeot 208');
        $interv2->setCreatedAt(new \DateTimeImmutable('2024-10-15'));
        $manager->persist($interv2);
        $intervenants[] = $interv2;

        $manager->flush();

        // ── Bénéficiaires ──
        $patientsData = [
            ['Simone Ruault', 'simone@mail.fr', '3 allée des Roses, 33100 Bordeaux',      '44.8378', '-0.5792', 'modere'],
            ['Denise Huard',  'denise@mail.fr', '9 rue des Cerises, 33000 Bordeaux',       '44.8340', '-0.5820', 'faible'],
            ['Fatima Musson', 'fatima@mail.fr', '21 rue Pasteur, 33600 Pessac',            '44.8050', '-0.6310', 'eleve'],
            ['Émile Neveu',   'emile@mail.fr',  '23 rue des Vignes, 33100 Bordeaux',       '44.8200', '-0.5700', 'modere'],
            ['Lucette Perrin','lucette@mail.fr','7 avenue de la Forêt, 33700 Mérignac',    '44.8390', '-0.6450', 'faible'],
            ['Marcel Bonin',  'marcel@mail.fr', '14 rue du Château, 33300 Bordeaux',       '44.8510', '-0.5950', 'modere'],
        ];

        $beneficiaires = [];
        foreach ($patientsData as [$nom, $email, $adresse, $lat, $lng, $risque]) {
            $u = new Utilisateur();
            $u->setNom($nom);
            $u->setEmail($email);
            $u->setMdp($this->hasher->hashPassword($u, 'Wecare1234!'));
            $u->setRole(RoleUtilisateur::Beneficiaire);
            $manager->persist($u);

            $b = new Beneficiaire();
            $b->setUtilisateur($u);
            $b->setAdminCreateur($admin);
            $b->setAdresse($adresse);
            $b->setDateNaissance(new \DateTime('1945-03-15'));
            $b->setLatitude($lat);
            $b->setLongitude($lng);
            $b->setNiveauRisque($risque);
            $manager->persist($b);
            $beneficiaires[] = $b;
        }
        $manager->flush();

        // ── Planning de la semaine courante ──
        $planningCourant = new Planning();
        $planningCourant->setAdmin($admin);
        $planningCourant->setSemaine(new \DateTime('monday this week'));
        $planningCourant->setStatut(StatutPlanning::Publie);
        $manager->persist($planningCourant);
        $manager->flush();

        // ── Interventions semaine courante (relatives à aujourd'hui) ──
        $slotsCourants = [
            [$beneficiaires[0], $interv1, '-1 day',  '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Terminee],
            [$beneficiaires[0], $interv1, 'today',   '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[0], $interv1, '+1 day',  '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[0], $interv1, '+2 days', '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[0], $interv1, '+3 days', '08:00', '09:00',  TypeIntervention::Toilette,       StatutIntervention::Planifiee],
            [$beneficiaires[1], $interv1, '-1 day',  '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Terminee],
            [$beneficiaires[1], $interv1, 'today',   '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Planifiee],
            [$beneficiaires[1], $interv1, '+1 day',  '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Planifiee],
            [$beneficiaires[1], $interv1, '+3 days', '09:00', '10:30',  TypeIntervention::Menage,         StatutIntervention::Planifiee],
            [$beneficiaires[2], $interv1, '-2 days', '14:00', '15:00',  TypeIntervention::Repas,          StatutIntervention::Terminee],
            [$beneficiaires[2], $interv1, 'today',   '14:00', '15:00',  TypeIntervention::Repas,          StatutIntervention::Planifiee],
            [$beneficiaires[2], $interv1, '+2 days', '14:00', '15:00',  TypeIntervention::Repas,          StatutIntervention::Planifiee],
            [$beneficiaires[3], $interv1, 'today',   '17:00', '18:00',  TypeIntervention::Accompagnement, StatutIntervention::Planifiee],
            [$beneficiaires[3], $interv1, '+2 days', '17:00', '18:00',  TypeIntervention::Accompagnement, StatutIntervention::Planifiee],
            [$beneficiaires[4], $interv2, 'today',   '10:00', '11:00',  TypeIntervention::Surveillance,   StatutIntervention::Planifiee],
            [$beneficiaires[4], $interv2, '+1 day',  '10:00', '11:00',  TypeIntervention::Surveillance,   StatutIntervention::Planifiee],
            [$beneficiaires[5], $interv2, 'today',   '11:30', '12:30',  TypeIntervention::Soins,          StatutIntervention::Planifiee],
            [$beneficiaires[5], $interv2, '+3 days', '11:30', '12:30',  TypeIntervention::Soins,          StatutIntervention::Planifiee],
        ];

        $interventionsCourantes = [];
        foreach ($slotsCourants as [$ben, $interv, $dayStr, $hDeb, $hFin, $type, $statut]) {
            $date = new \DateTime($dayStr);
            $i = new Intervention();
            $i->setBeneficiaire($ben);
            $i->setIntervenant($interv);
            $i->setDateDebut(new \DateTime($date->format('Y-m-d') . ' ' . $hDeb));
            $i->setDateFin(new \DateTime($date->format('Y-m-d') . ' ' . $hFin));
            $i->setTypeIntervention($type);
            $i->setStatut($statut);
            $i->setPlanning($planningCourant);
            $manager->persist($i);
            $interventionsCourantes[] = ['entity' => $i, 'statut' => $statut];
        }
        $manager->flush();

        // ── Comptes rendus semaine courante ──
        $crTexts = [
            'Simone bien réveillée. Toilette effectuée sans difficulté. Médicaments pris. RAS.',
            'Ménage effectué. Denise de bonne humeur. A demandé des yaourts pour la prochaine fois.',
            'Fatima fatiguée ce matin. Repas préparé. Légère tristesse mentionnée, à surveiller.',
        ];
        $idx = 0;
        foreach ($interventionsCourantes as $slot) {
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
        $manager->flush();

        // ── Données Juin 2026 ──
        $this->createMonthData($manager, $admin, $interv1, $interv2, $beneficiaires, '2026-06', [
            // [beneficiaire_idx, intervenant, jour (1-30), hDeb, hFin, type, terminee]
            [0, $interv1,  2, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  3, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  4, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  5, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  9, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 10, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 11, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 12, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 16, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 17, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 18, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 19, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 23, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 24, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 25, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 26, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1, 30, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [1, $interv1,  2, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1,  5, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1,  9, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1, 12, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1, 16, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1, 19, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1, 23, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1, 26, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1, 30, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [2, $interv1,  3, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1,  5, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1, 10, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1, 12, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1, 17, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1, 19, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1, 24, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1, 26, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [3, $interv1,  4, '17:00', '18:00',  TypeIntervention::Accompagnement, true],
            [3, $interv1, 11, '17:00', '18:00',  TypeIntervention::Accompagnement, true],
            [3, $interv1, 18, '17:00', '18:00',  TypeIntervention::Accompagnement, true],
            [3, $interv1, 25, '17:00', '18:00',  TypeIntervention::Accompagnement, true],
            [4, $interv2,  2, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2,  4, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2,  9, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2, 11, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2, 16, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2, 18, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2, 23, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2, 25, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [5, $interv2,  3, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2,  5, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2, 10, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2, 12, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2, 17, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2, 19, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2, 24, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2, 26, '11:30', '12:30',  TypeIntervention::Soins,          true],
        ], [
            'Simone en forme. Toilette complète effectuée. Prise de médicaments vérifiée. RAS.',
            'Ménage terminé. Denise contente de l\'état de l\'appartement. Pas de plainte.',
            'Fatima a bien mangé. Moral correct aujourd\'hui. A mentionné des douleurs aux genoux.',
            'Émile accompagné à sa consultation. Retour à 17h45. Bonne humeur.',
            'Lucette stable. Aucun signe inhabituel. Présence bien appréciée.',
            'Marcel : soins effectués correctement. Plaie en bonne voie de cicatrisation.',
            'Toilette et hydratation. Simone a bien dormi la nuit. Légère fatigue en matinée.',
            'Courses effectuées pour Denise. Liste respectée. A demandé du café décaféiné.',
            'Repas préparé et pris en compagnie. Fatima a apprécié le menu. Appétit correct.',
            'Visite de surveillance. Lucette dormait à l\'arrivée. Réveil calme. RAS.',
            'Soins de plaie. Marcel supporte bien le traitement. Progrès visibles.',
        ],
        $manager);

        // ── Données Juillet 2026 ──
        $this->createMonthData($manager, $admin, $interv1, $interv2, $beneficiaires, '2026-07', [
            [0, $interv1,  1, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  2, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  3, '08:00', '09:00',  TypeIntervention::Toilette,       true],
            [0, $interv1,  7, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1,  8, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1,  9, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 10, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 14, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 15, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 16, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 17, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 21, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 22, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 23, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 24, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 28, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 29, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 30, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [0, $interv1, 31, '08:00', '09:00',  TypeIntervention::Toilette,       false],
            [1, $interv1,  1, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1,  3, '09:00', '10:30',  TypeIntervention::Menage,         true],
            [1, $interv1,  7, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 10, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 14, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 17, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 21, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 24, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 28, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [1, $interv1, 31, '09:00', '10:30',  TypeIntervention::Menage,         false],
            [2, $interv1,  2, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1,  3, '14:00', '15:00',  TypeIntervention::Repas,          true],
            [2, $interv1,  8, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 10, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 15, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 17, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 22, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 24, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 29, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [2, $interv1, 31, '14:00', '15:00',  TypeIntervention::Repas,          false],
            [3, $interv1,  2, '17:00', '18:00',  TypeIntervention::Accompagnement, true],
            [3, $interv1,  9, '17:00', '18:00',  TypeIntervention::Accompagnement, false],
            [3, $interv1, 16, '17:00', '18:00',  TypeIntervention::Accompagnement, false],
            [3, $interv1, 23, '17:00', '18:00',  TypeIntervention::Accompagnement, false],
            [3, $interv1, 30, '17:00', '18:00',  TypeIntervention::Accompagnement, false],
            [4, $interv2,  1, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2,  3, '10:00', '11:00',  TypeIntervention::Surveillance,   true],
            [4, $interv2,  7, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2,  9, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2, 14, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2, 16, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2, 21, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2, 23, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2, 28, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [4, $interv2, 30, '10:00', '11:00',  TypeIntervention::Surveillance,   false],
            [5, $interv2,  2, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2,  3, '11:30', '12:30',  TypeIntervention::Soins,          true],
            [5, $interv2,  8, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 10, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 15, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 17, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 22, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 24, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 29, '11:30', '12:30',  TypeIntervention::Soins,          false],
            [5, $interv2, 31, '11:30', '12:30',  TypeIntervention::Soins,          false],
        ], [
            'Simone reposée après le week-end. Toilette effectuée avec bonne coopération.',
            'Denise bonne humeur, ménage complet. Fenêtres nettoyées à sa demande.',
            'Fatima a bien mangé. Légères douleurs abdominales mentionnées, transmis au coordinateur.',
            'Émile accompagné au marché. Très content de sortir. Bonne forme générale.',
            'Lucette stable. Surveillance normale. Aucun événement notable.',
            'Marcel : soins de plaie. Cicatrisation complète constatée ce jour.',
            'Toilette matinale. Simone fatiguée, a demandé à rester au lit plus longtemps.',
            'Ménage léger. Denise a évoqué une douleur au genou droit, à surveiller.',
        ],
        $manager);

        // ── Notifications ──
        $notifData = [
            [TypeNotification::Rappel, 'Visite prévue demain matin avec Simone Ruault — pensez à apporter le matériel de toilette.', false, '-1 day'],
            [TypeNotification::IncidentSignale, 'Un incident a été signalé pour Denise Huard et est en attente de traitement.', false, '-2 days'],
            [TypeNotification::CompteRenduValide, 'Votre compte rendu pour Fatima Musson du 26/05 a été validé par le coordinateur.', true, '-3 days'],
            [TypeNotification::Rappel, 'Rappel : réunion d\'équipe vendredi 6 juin à 14h.', true, '-5 days'],
            [TypeNotification::Rappel, 'Lucette Perrin : renouvellement du plan de soins prévu le 15 juillet.', false, '-7 days'],
        ];

        foreach ($notifData as [$type, $msg, $lu, $ago]) {
            $n = new Notification();
            $n->setUtilisateur($u1);
            $n->setType($type);
            $n->setLu($lu);
            $n->setMessage($msg);
            $n->setCreatedAt(new \DateTimeImmutable($ago));
            $manager->persist($n);
        }

        $manager->flush();
    }

    private function createMonthData(
        ObjectManager $manager,
        Administrateur $admin,
        Intervenant $interv1,
        Intervenant $interv2,
        array $beneficiaires,
        string $yearMonth,
        array $slots,
        array $crTexts,
        ObjectManager $mgr
    ): void {
        $planning = new Planning();
        $planning->setAdmin($admin);
        $planning->setSemaine(new \DateTime("$yearMonth-01"));
        $planning->setStatut(StatutPlanning::Publie);
        $manager->persist($planning);
        $manager->flush();

        $interventions = [];
        foreach ($slots as [$benIdx, $interv, $day, $hDeb, $hFin, $type, $terminee]) {
            $dateStr = sprintf('%s-%02d', $yearMonth, $day);
            $statut = $terminee ? StatutIntervention::Terminee : StatutIntervention::Planifiee;

            $i = new Intervention();
            $i->setBeneficiaire($beneficiaires[$benIdx]);
            $i->setIntervenant($interv);
            $i->setDateDebut(new \DateTime("$dateStr $hDeb"));
            $i->setDateFin(new \DateTime("$dateStr $hFin"));
            $i->setTypeIntervention($type);
            $i->setStatut($statut);
            $i->setPlanning($planning);
            $manager->persist($i);
            $interventions[] = ['entity' => $i, 'terminee' => $terminee];
        }
        $manager->flush();

        $idx = 0;
        foreach ($interventions as $slot) {
            if (!$slot['terminee']) continue;
            $textIdx = $idx % count($crTexts);
            $cr = new CompteRendu();
            $cr->setIntervention($slot['entity']);
            $cr->setContenu($crTexts[$textIdx]);
            $cr->setModeRedaction(ModeRedaction::Manuel);
            $cr->setDateRedaction(new \DateTime($slot['entity']->getDateDebut()->format('Y-m-d') . ' 20:00'));
            $cr->setValide(true);
            $manager->persist($cr);
            $idx++;
        }
        $manager->flush();
    }
}
