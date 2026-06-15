<?php

namespace App\Tests\Unit;

use App\Entity\Beneficiaire;
use App\Entity\Intervenant;
use App\Entity\Intervention;
use App\Entity\Utilisateur;
use App\Enum\GraviteIncident;
use App\Enum\ModeRedaction;
use App\Enum\NiveauAcces;
use App\Enum\RoleUtilisateur;
use App\Enum\StatutAbonnement;
use App\Enum\StatutEntreprise;
use App\Enum\StatutIncident;
use App\Enum\StatutIntervention;
use App\Enum\StatutPlanning;
use App\Enum\TypeIntervention;
use App\Enum\TypeMessage;
use App\Enum\TypeNotification;
use PHPUnit\Framework\TestCase;

class WeCareUnitTest extends TestCase
{
    // ═══════════════════════════════════════════════════════════
    //  ENUMS
    // ═══════════════════════════════════════════════════════════

    public function testRoleUtilisateurValues(): void
    {
        $this->assertSame('administrateur', RoleUtilisateur::Administrateur->value);
        $this->assertSame('intervenant',    RoleUtilisateur::Intervenant->value);
        $this->assertSame('beneficiaire',   RoleUtilisateur::Beneficiaire->value);
    }

    public function testRoleUtilisateurFrom(): void
    {
        $this->assertSame(RoleUtilisateur::Intervenant,    RoleUtilisateur::from('intervenant'));
        $this->assertSame(RoleUtilisateur::Administrateur, RoleUtilisateur::from('administrateur'));
        $this->assertSame(RoleUtilisateur::Beneficiaire,   RoleUtilisateur::from('beneficiaire'));
    }

    public function testRoleUtilisateurTryFromInvalid(): void
    {
        $this->assertNull(RoleUtilisateur::tryFrom('inconnu'));
    }

    public function testNiveauAccesValues(): void
    {
        $this->assertSame('lecture',     NiveauAcces::Lecture->value);
        $this->assertSame('standard',    NiveauAcces::Standard->value);
        $this->assertSame('super_admin', NiveauAcces::SuperAdmin->value);
    }

    public function testStatutInterventionValues(): void
    {
        $this->assertSame('planifiee', StatutIntervention::Planifiee->value);
        $this->assertSame('en_cours',  StatutIntervention::EnCours->value);
        $this->assertSame('terminee',  StatutIntervention::Terminee->value);
        $this->assertSame('annulee',   StatutIntervention::Annulee->value);
    }

    public function testStatutInterventionCasesCount(): void
    {
        $this->assertCount(4, StatutIntervention::cases());
    }

    public function testTypeInterventionValues(): void
    {
        $this->assertSame('menage',          TypeIntervention::Menage->value);
        $this->assertSame('toilette',        TypeIntervention::Toilette->value);
        $this->assertSame('repas',           TypeIntervention::Repas->value);
        $this->assertSame('accompagnement',  TypeIntervention::Accompagnement->value);
        $this->assertSame('surveillance',    TypeIntervention::Surveillance->value);
        $this->assertSame('soins',           TypeIntervention::Soins->value);
    }

    public function testTypeInterventionCasesCount(): void
    {
        $this->assertCount(6, TypeIntervention::cases());
    }

    public function testGraviteIncidentValues(): void
    {
        $this->assertSame('faible',   GraviteIncident::Faible->value);
        $this->assertSame('moderee',  GraviteIncident::Moderee->value);
        $this->assertSame('grave',    GraviteIncident::Grave->value);
        $this->assertSame('critique', GraviteIncident::Critique->value);
    }

    public function testStatutIncidentValues(): void
    {
        $this->assertSame('signale',       StatutIncident::Signale->value);
        $this->assertSame('en_traitement', StatutIncident::EnTraitement->value);
        $this->assertSame('resolu',        StatutIncident::Resolu->value);
        $this->assertSame('cloture',       StatutIncident::Cloture->value);
    }

    public function testStatutPlanningValues(): void
    {
        $this->assertSame('brouillon', StatutPlanning::Brouillon->value);
        $this->assertSame('publie',    StatutPlanning::Publie->value);
        $this->assertSame('archive',   StatutPlanning::Archive->value);
    }

    public function testStatutAbonnementValues(): void
    {
        $this->assertSame('actif',    StatutAbonnement::Actif->value);
        $this->assertSame('inactif',  StatutAbonnement::Inactif->value);
        $this->assertSame('suspendu', StatutAbonnement::Suspendu->value);
        $this->assertSame('expire',   StatutAbonnement::Expire->value);
    }

    public function testStatutEntrepriseValues(): void
    {
        $this->assertSame('active',    StatutEntreprise::Active->value);
        $this->assertSame('inactive',  StatutEntreprise::Inactive->value);
        $this->assertSame('suspendue', StatutEntreprise::Suspendue->value);
    }

    public function testTypeNotificationValues(): void
    {
        $this->assertSame('compte_rendu_soumis',  TypeNotification::CompteRenduSoumis->value);
        $this->assertSame('compte_rendu_valide',  TypeNotification::CompteRenduValide->value);
        $this->assertSame('incident_signale',     TypeNotification::IncidentSignale->value);
        $this->assertSame('rappel',               TypeNotification::Rappel->value);
    }

    public function testTypeMessageValues(): void
    {
        $this->assertSame('direct',  TypeMessage::Direct->value);
        $this->assertSame('systeme', TypeMessage::Systeme->value);
        $this->assertSame('alerte',  TypeMessage::Alerte->value);
    }

    public function testModeRedactionValues(): void
    {
        $this->assertSame('manuel',     ModeRedaction::Manuel->value);
        $this->assertSame('vocal',      ModeRedaction::Vocal->value);
        $this->assertSame('assiste_ia', ModeRedaction::AssistelA->value);
    }

    // ═══════════════════════════════════════════════════════════
    //  UTILISATEUR
    // ═══════════════════════════════════════════════════════════

    public function testUtilisateurGettersSetters(): void
    {
        $u = new Utilisateur();
        $u->setNom('Jean Dupont');
        $u->setEmail('jean@wecare.fr');
        $u->setMdp('hashed_password');
        $u->setRole(RoleUtilisateur::Intervenant);

        $this->assertSame('Jean Dupont',     $u->getNom());
        $this->assertSame('jean@wecare.fr',  $u->getEmail());
        $this->assertSame('hashed_password', $u->getMdp());
        $this->assertSame(RoleUtilisateur::Intervenant, $u->getRole());
        $this->assertNull($u->getId());
    }

    public function testUtilisateurGetRolesAdmin(): void
    {
        $u = new Utilisateur();
        $u->setRole(RoleUtilisateur::Administrateur);

        $this->assertSame(['ROLE_ADMINISTRATEUR'], $u->getRoles());
    }

    public function testUtilisateurGetRolesIntervenant(): void
    {
        $u = new Utilisateur();
        $u->setRole(RoleUtilisateur::Intervenant);

        $this->assertSame(['ROLE_INTERVENANT'], $u->getRoles());
    }

    public function testUtilisateurGetRolesBeneficiaire(): void
    {
        $u = new Utilisateur();
        $u->setRole(RoleUtilisateur::Beneficiaire);

        $this->assertSame(['ROLE_BENEFICIAIRE'], $u->getRoles());
    }

    public function testUtilisateurGetUserIdentifier(): void
    {
        $u = new Utilisateur();
        $u->setEmail('ident@wecare.fr');

        $this->assertSame('ident@wecare.fr', $u->getUserIdentifier());
    }

    public function testUtilisateurGetPasswordReturnsMdp(): void
    {
        $u = new Utilisateur();
        $u->setMdp('secret_hash');

        $this->assertSame('secret_hash', $u->getPassword());
    }

    public function testUtilisateurEraseCredentialsIsNoop(): void
    {
        $u = new Utilisateur();
        $u->setMdp('secret_hash');
        $u->eraseCredentials();

        // eraseCredentials ne doit pas effacer le mot de passe haché
        $this->assertSame('secret_hash', $u->getMdp());
    }

    public function testUtilisateurCollectionsInitialisees(): void
    {
        $u = new Utilisateur();

        $this->assertCount(0, $u->getNotifications());
        $this->assertCount(0, $u->getMessagesEnvoyes());
        $this->assertCount(0, $u->getMessagesRecus());
    }

    public function testUtilisateurEntrepriseNullParDefaut(): void
    {
        $u = new Utilisateur();

        $this->assertNull($u->getEntreprise());
    }

    // ═══════════════════════════════════════════════════════════
    //  INTERVENANT — logique métier setStatut()
    // ═══════════════════════════════════════════════════════════

    public function testIntervenantStatutParDefautEstActif(): void
    {
        $iv = new Intervenant();

        $this->assertSame('actif', $iv->getStatut());
    }

    public function testIntervenantSetStatutActifRendDisponible(): void
    {
        $iv = new Intervenant();
        $iv->setStatut('actif');

        $this->assertSame('actif', $iv->getStatut());
        $this->assertTrue($iv->isDisponibilite());
    }

    public function testIntervenantSetStatutCongeRendIndisponible(): void
    {
        $iv = new Intervenant();
        $iv->setStatut('conge');

        $this->assertSame('conge', $iv->getStatut());
        $this->assertFalse($iv->isDisponibilite());
    }

    public function testIntervenantSetStatutArretRendIndisponible(): void
    {
        $iv = new Intervenant();
        $iv->setStatut('arret');

        $this->assertFalse($iv->isDisponibilite());
    }

    public function testIntervenantSetStatutInactifRendIndisponible(): void
    {
        $iv = new Intervenant();
        $iv->setStatut('inactif');

        $this->assertFalse($iv->isDisponibilite());
    }

    public function testIntervenantTransitionCongeVersActif(): void
    {
        $iv = new Intervenant();
        $iv->setStatut('conge');
        $this->assertFalse($iv->isDisponibilite());

        $iv->setStatut('actif');
        $this->assertTrue($iv->isDisponibilite());
    }

    public function testIntervenantTransitionActifVersConge(): void
    {
        $iv = new Intervenant();
        $iv->setStatut('actif');
        $this->assertTrue($iv->isDisponibilite());

        $iv->setStatut('conge');
        $this->assertFalse($iv->isDisponibilite());
    }

    public function testIntervenantGettersSetters(): void
    {
        $iv = new Intervenant();
        $iv->setSpecialite('Soins infirmiers');
        $iv->setTelephone('0612345678');
        $iv->setRayonIntervention(25.0);
        $iv->setVehicule('Peugeot 208');

        $this->assertSame('Soins infirmiers', $iv->getSpecialite());
        $this->assertSame('0612345678',       $iv->getTelephone());
        $this->assertSame(25.0,               $iv->getRayonIntervention());
        $this->assertSame('Peugeot 208',      $iv->getVehicule());
    }

    public function testIntervenantVehiculeNullable(): void
    {
        $iv = new Intervenant();
        $iv->setVehicule(null);

        $this->assertNull($iv->getVehicule());
    }

    public function testIntervenantCreatedAt(): void
    {
        $iv   = new Intervenant();
        $date = new \DateTimeImmutable('2025-09-01');
        $iv->setCreatedAt($date);

        $this->assertSame($date, $iv->getCreatedAt());
    }

    public function testIntervenantCreatedAtNullable(): void
    {
        $iv = new Intervenant();
        $iv->setCreatedAt(null);

        $this->assertNull($iv->getCreatedAt());
    }

    public function testIntervenantCollectionsInitialisees(): void
    {
        $iv = new Intervenant();

        $this->assertCount(0, $iv->getPlannings());
        $this->assertCount(0, $iv->getInterventions());
    }

    // ═══════════════════════════════════════════════════════════
    //  BENEFICIAIRE
    // ═══════════════════════════════════════════════════════════

    public function testBeneficiaireGettersSetters(): void
    {
        $b  = new Beneficiaire();
        $dn = new \DateTime('1960-03-12');

        $b->setDateNaissance($dn);
        $b->setAdresse('3 allée des Roses, 33100 Bordeaux');
        $b->setLatitude('44.8378000');
        $b->setLongitude('-0.5792000');
        $b->setNiveauRisque('modere');
        $b->setPathologie('Alzheimer débutant');
        $b->setRdvParSemaine(3);

        $this->assertSame($dn,                              $b->getDateNaissance());
        $this->assertSame('3 allée des Roses, 33100 Bordeaux', $b->getAdresse());
        $this->assertSame('44.8378000',                     $b->getLatitude());
        $this->assertSame('-0.5792000',                     $b->getLongitude());
        $this->assertSame('modere',                         $b->getNiveauRisque());
        $this->assertSame('Alzheimer débutant',             $b->getPathologie());
        $this->assertSame(3,                                $b->getRdvParSemaine());
    }

    public function testBeneficiairePathologieNullable(): void
    {
        $b = new Beneficiaire();
        $b->setPathologie(null);

        $this->assertNull($b->getPathologie());
    }

    public function testBeneficiaireRdvParSemaineNullable(): void
    {
        $b = new Beneficiaire();
        $b->setRdvParSemaine(null);

        $this->assertNull($b->getRdvParSemaine());
    }

    public function testBeneficiaireSoignantReferentNullParDefaut(): void
    {
        $b = new Beneficiaire();

        $this->assertNull($b->getSoignantReferent());
    }

    public function testBeneficiaireSoignantReferentAssignable(): void
    {
        $b  = new Beneficiaire();
        $iv = new Intervenant();
        $b->setSoignantReferent($iv);

        $this->assertSame($iv, $b->getSoignantReferent());
    }

    public function testBeneficiaireSoignantReferentRemovable(): void
    {
        $b  = new Beneficiaire();
        $iv = new Intervenant();
        $b->setSoignantReferent($iv);
        $b->setSoignantReferent(null);

        $this->assertNull($b->getSoignantReferent());
    }

    public function testBeneficiaireCollectionsInitialisees(): void
    {
        $b = new Beneficiaire();

        $this->assertCount(0, $b->getInterventions());
        $this->assertCount(0, $b->getMessages());
        $this->assertCount(0, $b->getAidants());
    }

    public function testBeneficiaireNiveauRisqueCritique(): void
    {
        $b = new Beneficiaire();
        $b->setNiveauRisque('critique');

        $this->assertSame('critique', $b->getNiveauRisque());
    }

    // ═══════════════════════════════════════════════════════════
    //  INTERVENTION
    // ═══════════════════════════════════════════════════════════

    public function testInterventionGettersSetters(): void
    {
        $i     = new Intervention();
        $debut = new \DateTime('2025-06-10 09:00:00');
        $fin   = new \DateTime('2025-06-10 11:00:00');

        $i->setDateDebut($debut);
        $i->setDateFin($fin);
        $i->setStatut(StatutIntervention::Planifiee);
        $i->setTypeIntervention(TypeIntervention::Menage);
        $i->setObservations('Ménage effectué, cuisine et salon.');

        $this->assertSame($debut,                           $i->getDateDebut());
        $this->assertSame($fin,                             $i->getDateFin());
        $this->assertSame(StatutIntervention::Planifiee,    $i->getStatut());
        $this->assertSame(TypeIntervention::Menage,         $i->getTypeIntervention());
        $this->assertSame('Ménage effectué, cuisine et salon.', $i->getObservations());
    }

    public function testInterventionObservationsNullable(): void
    {
        $i = new Intervention();
        $i->setObservations(null);

        $this->assertNull($i->getObservations());
    }

    public function testInterventionCollectionIncidentsInitialisee(): void
    {
        $i = new Intervention();

        $this->assertCount(0, $i->getIncidents());
    }

    public function testInterventionTransitionsPlanifieeEnCours(): void
    {
        $i = new Intervention();
        $i->setStatut(StatutIntervention::Planifiee);
        $i->setStatut(StatutIntervention::EnCours);

        $this->assertSame(StatutIntervention::EnCours, $i->getStatut());
    }

    public function testInterventionTransitionEnCoursTerminee(): void
    {
        $i = new Intervention();
        $i->setStatut(StatutIntervention::EnCours);
        $i->setStatut(StatutIntervention::Terminee);

        $this->assertSame(StatutIntervention::Terminee, $i->getStatut());
    }

    public function testInterventionTransitionAnnulee(): void
    {
        $i = new Intervention();
        $i->setStatut(StatutIntervention::Planifiee);
        $i->setStatut(StatutIntervention::Annulee);

        $this->assertSame(StatutIntervention::Annulee, $i->getStatut());
    }

    public function testInterventionTousTypesAssignables(): void
    {
        $i = new Intervention();

        foreach (TypeIntervention::cases() as $type) {
            $i->setTypeIntervention($type);
            $this->assertSame($type, $i->getTypeIntervention());
        }
    }

    public function testInterventionIntervenantNullParDefaut(): void
    {
        $i = new Intervention();

        $this->assertNull($i->getIntervenant());
    }

    public function testInterventionAssociationIntervenant(): void
    {
        $i  = new Intervention();
        $iv = new Intervenant();
        $i->setIntervenant($iv);

        $this->assertSame($iv, $i->getIntervenant());
    }

    public function testInterventionAssociationBeneficiaire(): void
    {
        $i = new Intervention();
        $b = new Beneficiaire();
        $i->setBeneficiaire($b);

        $this->assertSame($b, $i->getBeneficiaire());
    }
}
