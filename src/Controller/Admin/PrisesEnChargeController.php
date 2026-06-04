<?php

namespace App\Controller\Admin;

use App\Entity\Aidant;
use App\Entity\Intervention;
use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use App\Repository\AidantRepository;
use App\Repository\BeneficiaireRepository;
use App\Repository\IntervenantRepository;
use App\Repository\InterventionRepository;
use App\Repository\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class PrisesEnChargeController extends AbstractController
{
    use AdminTrait;


    #[Route('/prises-en-charge', name: 'admin_prises_en_charge')]
    public function index(
        Request                $request,
        AidantRepository       $aidantRepo,
        BeneficiaireRepository $benRepo,
        IntervenantRepository  $intervenantRepo,
        InterventionRepository $interventionRepo,
    ): Response {
        $admin = $this->getCurrentAdmin();

        // Date sélectionnée
        $dateStr = $request->query->get('date', (new \DateTime('today'))->format('Y-m-d'));
        $date    = new \DateTime($dateStr);
        $prevDate = (clone $date)->modify('-1 day');
        $nextDate = (clone $date)->modify('+1 day');
        $dateLabel = self::JOURS_COURTS[(int) $date->format('w')]
            . ' ' . $date->format('j') . ' ' . self::MOIS_COURTS[(int) $date->format('n')]
            . ' ' . $date->format('Y');

        // Aidants + bénéficiaires filtrés par admin
        $aidants          = $aidantRepo->findAllWithDetails($admin);
        $benIdsWithAidant = array_map(fn($a) => $a->getBeneficiaire()->getId(), $aidants);

        // Interventions à venir (30j) pour afficher dans le panneau
        $allUpcoming   = $interventionRepo->findUpcomingForBeneficiaires($benIdsWithAidant, 30);
        $upcomingByBen = [];
        foreach ($allUpcoming as $interv) {
            $upcomingByBen[$interv->getBeneficiaire()->getId()][] = $interv;
        }

        // Tâches non assignées (30 jours) groupées par bénéficiaire
        $allUnassigned    = $interventionRepo->findUnassignedUpcoming($benIdsWithAidant, 30);
        $unassignedByBen  = [];
        foreach ($allUnassigned as $u) {
            $unassignedByBen[$u->getBeneficiaire()->getId()][] = $u;
        }

        // Interventions du jour pour tous les bénéficiaires admin → qui a déjà rdv aujourd'hui
        $allIvIds      = array_map(fn($iv) => $iv->getId(), $intervenantRepo->findAllWithDetails($admin));
        $intervJour    = $interventionRepo->findForDate($date, $allIvIds ?: null);
        $benIdsAvecRdv = array_unique(array_map(fn($i) => $i->getBeneficiaire()->getId(), $intervJour));

        $joursLbl = self::JOURS_COURTS;
        $moisLbl  = self::MOIS_COURTS;

        $rows = [];
        foreach ($aidants as $aidant) {
            $ben = $aidant->getBeneficiaire();
            $age = (int) (new \DateTime())->diff($ben->getDateNaissance())->y;
            $ref = $ben->getSoignantReferent();

            $refData = null;
            if ($ref) {
                $nom   = $ref->getUtilisateur()->getNom();
                $refData = [
                    'id'        => $ref->getId(),
                    'nom'       => $nom,
                    'initiales' => $this->getInitiales($nom),
                    'specialite'=> $ref->getSpecialite() ?? 'Soignant',
                ];
            }

            $benUpcoming   = array_slice($upcomingByBen[$ben->getId()] ?? [], 0, 10);
            $interventions = array_map(function ($i) use ($joursLbl, $moisLbl) {
                $d = $i->getDateDebut();
                return [
                    'id'      => $i->getId(),
                    'jour'    => $joursLbl[(int) $d->format('w')],
                    'date'    => $d->format('j') . ' ' . $moisLbl[(int) $d->format('n') - 1],
                    'hDebut'  => $d->format('H:i'),
                    'hFin'    => $i->getDateFin()->format('H:i'),
                    'soignant'=> $i->getIntervenant()->getUtilisateur()->getNom(),
                    'type'    => ucfirst($i->getTypeIntervention()->value),
                    'statut'  => $i->getStatut()->value,
                ];
            }, $benUpcoming);

            $besoinAujourdhui = ($ben->getRdvParSemaine() ?? 0) > 0
                && !in_array($ben->getId(), $benIdsAvecRdv);

            $rows[] = [
                'id'              => $aidant->getId(),
                'benId'           => $ben->getId(),
                'aidantNom'       => $aidant->getNomComplet(),
                'aidantPrenom'    => $aidant->getPrenom() ?? '',
                'aidantNomFamille'=> $aidant->getNom() ?? '',
                'telephone'       => $aidant->getTelephone() ?? '',
                'email'           => $aidant->getEmail() ?? '',
                'lien'            => $aidant->getLien() ?? '',
                'patientNom'      => $ben->getUtilisateur()->getNom(),
                'patientAge'      => $age,
                'adresse'         => $ben->getAdresse() ?? '',
                'niveau'          => $ben->getNiveauRisque() ?? 'faible',
                'actif'           => $ref !== null,
                'ref'             => $refData,
                'interventions'   => $interventions,
                'nbInterventions' => count($interventions),
                'rdvParSemaine'   => $ben->getRdvParSemaine() ?? 0,
                'besoinAujourdhui'=> $besoinAujourdhui,
                'tachesNonAssignees' => array_map(function ($i) use ($joursLbl, $moisLbl) {
                    $d = $i->getDateDebut();
                    return [
                        'id'    => $i->getId(),
                        'jour'  => $joursLbl[(int) $d->format('w')],
                        'date'  => $d->format('j') . ' ' . $moisLbl[(int) $d->format('n') - 1],
                        'hDebut'=> $d->format('H:i'),
                        'hFin'  => $i->getDateFin()->format('H:i'),
                        'type'  => ucfirst($i->getTypeIntervention()->value),
                    ];
                }, $unassignedByBen[$ben->getId()] ?? []),
            ];
        }

        // Soignants filtrés par admin + leurs heures occupées pour la date
        $soignantsData = [];
        foreach ($intervenantRepo->findAllWithDetails($admin) as $idx => $iv) {
            $nom   = $iv->getUtilisateur()->getNom();

            // Heures occupées pour la date sélectionnée
            $occupiedHours = [];
            foreach ($intervJour as $interv) {
                if ($interv->getIntervenant()->getId() === $iv->getId()) {
                    $hDeb = (int) $interv->getDateDebut()->format('H');
                    $hFin = (int) $interv->getDateFin()->format('H');
                    for ($h = $hDeb; $h < $hFin; $h++) {
                        $occupiedHours[] = $h;
                    }
                }
            }

            $soignantsData[] = [
                'id'            => $iv->getId(),
                'nom'           => $nom,
                'initiales'     => $this->getInitiales($nom),
                'color'         => self::PALETTE[$idx % count(self::PALETTE)],
                'specialite'    => $iv->getSpecialite() ?? 'Soignant',
                'disponible'    => $iv->isDisponibilite(),
                'statut'        => $iv->getStatut(),
                'rayon'         => (float) ($iv->getRayonIntervention() ?? 0),
                'occupiedHours' => array_unique($occupiedHours),
            ];
        }

        // Patients sans prise en charge (pour créer une nouvelle)
        $beneficiaires = [];
        foreach ($benRepo->findAllNames($admin) as $ben) {
            if (!in_array($ben->getId(), $benIdsWithAidant)) {
                $beneficiaires[] = ['id' => $ben->getId(), 'nom' => $ben->getUtilisateur()->getNom()];
            }
        }

        $nbBesoin = count(array_filter($rows, fn($r) => $r['besoinAujourdhui']));
        $openRow  = (int) $request->query->get('open', 0);

        return $this->render('admin/prises_en_charge.html.twig', [
            'rows'          => $rows,
            'soignantsData' => $soignantsData,
            'beneficiaires' => $beneficiaires,
            'date'          => $date->format('Y-m-d'),
            'dateLabel'     => $dateLabel,
            'prevUrl'       => $this->generateUrl('admin_prises_en_charge', ['date' => $prevDate->format('Y-m-d')]),
            'nextUrl'       => $this->generateUrl('admin_prises_en_charge', ['date' => $nextDate->format('Y-m-d')]),
            'isToday'       => $date->format('Y-m-d') === (new \DateTime('today'))->format('Y-m-d'),
            'nbBesoin'      => $nbBesoin,
            'openRow'       => $openRow,
        ]);
    }

    #[Route('/prises-en-charge/planifier', name: 'admin_prises_en_charge_planifier', methods: ['POST'])]
    public function planifier(
        Request                $request,
        BeneficiaireRepository $benRepo,
        IntervenantRepository  $intervenantRepo,
        InterventionRepository $interventionRepo,
        PlanningRepository     $planningRepo,
        EntityManagerInterface $em,
    ): Response {
        $benId   = (int) $request->request->get('ben_id');
        $ivId    = (int) $request->request->get('iv_id');
        $dateStr = $request->request->get('date', (new \DateTime('today'))->format('Y-m-d'));
        $hDebut  = (int) $request->request->get('h_debut', 9);
        $hFin    = $hDebut + 1;
        $type    = $request->request->get('type', 'soins');

        $rowId  = $request->request->get('row_id');

        $ben = $benRepo->find($benId);
        $iv  = $intervenantRepo->find($ivId);
        if (!$ben || !$iv) {
            return $this->redirectToRoute('admin_prises_en_charge', ['date' => $dateStr]);
        }

        $date      = new \DateTime($dateStr);
        $dateDebut = (clone $date)->setTime($hDebut, 0);
        $dateFin   = (clone $date)->setTime($hFin, 0);

        $redirect = array_filter(['date' => $dateStr, 'open' => $rowId]);

        if (!$iv->isDisponibilite()) {
            $this->addFlash('error', $iv->getUtilisateur()->getNom() . ' n\'est pas disponible actuellement.');
            return $this->redirectToRoute('admin_prises_en_charge', $redirect);
        }
        if ($interventionRepo->isIntervenantBusy($iv, $dateDebut, $dateFin)) {
            $this->addFlash('error', $iv->getUtilisateur()->getNom() . ' est déjà occupé(e) sur ce créneau.');
            return $this->redirectToRoute('admin_prises_en_charge', $redirect);
        }

        $planning = $this->findOrCreatePlanning($date, $planningRepo, $em);

        $interv = new Intervention();
        $interv->setBeneficiaire($ben);
        $interv->setIntervenant($iv);
        $interv->setPlanning($planning);
        $interv->setDateDebut($dateDebut);
        $interv->setDateFin($dateFin);
        $interv->setStatut(StatutIntervention::Planifiee);
        $interv->setTypeIntervention(TypeIntervention::from($type));
        $em->persist($interv);

        if ($ben->getSoignantReferent() === null) {
            $ben->setSoignantReferent($iv);
        }

        $em->flush();

        return $this->redirectToRoute('admin_prises_en_charge', array_filter(['date' => $dateStr, 'open' => $rowId]));
    }

    #[Route('/prises-en-charge/tache/creer', name: 'admin_pec_tache_creer', methods: ['POST'])]
    public function tacheCreer(
        Request                $request,
        BeneficiaireRepository $benRepo,
        InterventionRepository $interventionRepo,
        PlanningRepository     $planningRepo,
        EntityManagerInterface $em,
    ): Response {
        $benId   = (int) $request->request->get('ben_id');
        $dateStr = $request->request->get('tache_date', (new \DateTime('today'))->format('Y-m-d'));
        $hDebut  = (int) $request->request->get('h_debut', 9);
        $type    = $request->request->get('type', 'soins');
        $pecDate = $request->request->get('pec_date', (new \DateTime('today'))->format('Y-m-d'));
        $rowId   = $request->request->get('row_id');

        $ben = $benRepo->find($benId);
        if (!$ben) {
            return $this->redirectToRoute('admin_prises_en_charge', ['date' => $pecDate]);
        }

        $date      = new \DateTime($dateStr);
        $dateDebut = (clone $date)->setTime($hDebut, 0);
        $dateFin   = (clone $date)->setTime($hDebut + 1, 0);

        $planning = $this->findOrCreatePlanning($date, $planningRepo, $em);

        $interv = new Intervention();
        $interv->setBeneficiaire($ben);
        $interv->setIntervenant(null);
        $interv->setPlanning($planning);
        $interv->setDateDebut($dateDebut);
        $interv->setDateFin($dateFin);
        $interv->setStatut(StatutIntervention::Planifiee);
        $interv->setTypeIntervention(TypeIntervention::from($type));
        $em->persist($interv);
        $em->flush();

        return $this->redirectToRoute('admin_prises_en_charge', array_filter(['date' => $pecDate, 'open' => $rowId]));
    }

    #[Route('/prises-en-charge/intervention/{id}/annuler', name: 'admin_pec_intervention_annuler', methods: ['POST'])]
    public function annulerIntervention(
        int                    $id,
        InterventionRepository $interventionRepo,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $interv = $interventionRepo->find($id);
        $date   = $request->request->get('date', (new \DateTime('today'))->format('Y-m-d'));
        $rowId  = $request->request->get('row_id');
        if ($interv) {
            $em->remove($interv);
            $em->flush();
        }
        return $this->redirectToRoute('admin_prises_en_charge', array_filter(['date' => $date, 'open' => $rowId]));
    }

    #[Route('/prises-en-charge/ajouter', name: 'admin_prises_en_charge_ajouter', methods: ['POST'])]
    public function ajouter(
        Request                $request,
        BeneficiaireRepository $benRepo,
        AidantRepository       $aidantRepo,
        EntityManagerInterface $em,
    ): Response {
        $benId = (int) $request->request->get('ben_id');
        $nom   = trim($request->request->get('nom', ''));
        if (!$nom) return $this->redirectToRoute('admin_prises_en_charge');

        $ben = $benRepo->find($benId);
        if (!$ben) return $this->redirectToRoute('admin_prises_en_charge');

        if ($aidantRepo->findOneBy(['beneficiaire' => $ben])) {
            return $this->redirectToRoute('admin_prises_en_charge');
        }

        $aidant = new Aidant();
        $aidant->setBeneficiaire($ben);
        $aidant->setNom($nom);
        $aidant->setPrenom(trim($request->request->get('prenom', '')) ?: null);
        $aidant->setTelephone(trim($request->request->get('telephone', '')) ?: null);
        $aidant->setEmail(trim($request->request->get('email', '')) ?: null);
        $aidant->setLien(trim($request->request->get('lien', '')) ?: null);
        $em->persist($aidant);
        $em->flush();

        return $this->redirectToRoute('admin_prises_en_charge');
    }

    #[Route('/prises-en-charge/{id}/modifier', name: 'admin_prises_en_charge_modifier', methods: ['POST'])]
    public function modifier(
        int                    $id,
        Request                $request,
        AidantRepository       $aidantRepo,
        EntityManagerInterface $em,
    ): Response {
        $aidant = $aidantRepo->find($id);
        if (!$aidant) return $this->redirectToRoute('admin_prises_en_charge');

        $aidant->setNom(trim($request->request->get('nom', $aidant->getNom())));
        $aidant->setPrenom(trim($request->request->get('prenom', '')) ?: null);
        $aidant->setTelephone(trim($request->request->get('telephone', '')) ?: null);
        $aidant->setEmail(trim($request->request->get('email', '')) ?: null);
        $aidant->setLien(trim($request->request->get('lien', '')) ?: null);
        $em->flush();

        return $this->redirectToRoute('admin_prises_en_charge');
    }

    #[Route('/prises-en-charge/{id}/supprimer', name: 'admin_prises_en_charge_supprimer', methods: ['POST'])]
    public function supprimer(
        int                    $id,
        AidantRepository       $aidantRepo,
        EntityManagerInterface $em,
    ): Response {
        $aidant = $aidantRepo->find($id);
        if ($aidant) {
            $em->remove($aidant);
            $em->flush();
        }
        return $this->redirectToRoute('admin_prises_en_charge');
    }

    #[Route('/prises-en-charge/assigner', name: 'admin_prises_en_charge_assigner', methods: ['POST'])]
    public function assigner(
        Request                $request,
        BeneficiaireRepository $benRepo,
        IntervenantRepository  $intervenantRepo,
        EntityManagerInterface $em,
    ): Response {
        $benId      = (int) $request->request->get('ben_id');
        $soignantId = (int) $request->request->get('soignant_id');

        $ben = $benRepo->find($benId);
        if (!$ben) return $this->redirectToRoute('admin_prises_en_charge');

        $iv = $soignantId > 0 ? $intervenantRepo->find($soignantId) : null;
        $ben->setSoignantReferent($iv);
        $em->flush();

        return $this->redirectToRoute('admin_prises_en_charge');
    }
}
