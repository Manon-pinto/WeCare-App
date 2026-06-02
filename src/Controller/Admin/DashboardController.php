<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Enum\StatutIntervention;
use App\Repository\BeneficiaireRepository;
use App\Repository\IncidentRepository;
use App\Repository\IntervenantRepository;
use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    use AdminTrait;

    private const JOURS   = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    private const MOIS    = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                             'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    private const PALETTE = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(
        Request                $request,
        IntervenantRepository  $intervenantRepo,
        BeneficiaireRepository $beneficiaireRepo,
        InterventionRepository $interventionRepo,
        IncidentRepository     $incidentRepo,
    ): Response {
        /** @var Utilisateur $user */
        $user   = $this->getUser();
        $admin  = $this->getCurrentAdmin();
        $prenom = explode(' ', trim($user?->getNom() ?? ''))[0] ?? '';

        // Intervenants de cet admin
        $mesIntervenants = $intervenantRepo->findAllWithDetails($admin);
        $myIvIds         = array_map(fn($iv) => $iv->getId(), $mesIntervenants);

        // ── Date de navigation ─────────────────────────────────────────────
        $dateStr = $request->query->get('date', 'today');
        try { $date = new \DateTime($dateStr); } catch (\Exception) { $date = new \DateTime('today'); }
        $date->setTime(0, 0, 0);
        $prevDate = (clone $date)->modify('-1 day');
        $nextDate = (clone $date)->modify('+1 day');

        // ── Planning du jour filtré par admin ─────────────────────────────
        $interventionsJour = $interventionRepo->findForDate($date, $myIvIds);

        // ── Stats dérivées des données déjà chargées ──────────────────────
        $nbIntervenants  = count($mesIntervenants);
        $nbBeneficiaires = count($beneficiaireRepo->findAllNames($admin));
        $nbAujourdhui    = count($interventionsJour);
        $nbTerminees     = count(array_filter(
            $interventionsJour,
            fn($i) => $i->getStatut() === StatutIntervention::Terminee
        ));

        // ── Données pour la modale "Nouvelle intervention" ────────────────
        $patients  = array_map(fn($b) => ['id' => $b->getId(), 'nom' => $b->getUtilisateur()->getNom()],
                               $beneficiaireRepo->findAllNames($admin));
        $soignants = array_map(fn($iv) => [
            'id'        => $iv->getId(),
            'nom'       => $iv->getUtilisateur()->getNom(),
            'specialite'=> $iv->getSpecialite() ?? '',
        ], $mesIntervenants);

        // Heures occupées aujourd'hui par soignant (pour blocage client)
        $occupiedByIv = [];
        foreach ($mesIntervenants as $iv) {
            $occupiedByIv[$iv->getId()] = [];
        }
        foreach ($interventionsJour as $interv) {
            $ivId  = $interv->getIntervenant()->getId();
            $hDeb  = (int) $interv->getDateDebut()->format('H');
            $hFin  = (int) $interv->getDateFin()->format('H');
            for ($h = $hDeb; $h < $hFin; $h++) {
                $occupiedByIv[$ivId][] = $h;
            }
        }

        // ── Alertes (incidents globaux) ────────────────────────────────────
        $alertes = array_map(static function ($incident) {
            $gravite = $incident->getGravite()?->value ?? 'moderee';
            $zone    = $incident->getZone();
            $titre   = $zone ? 'Incident — ' . $zone : 'Incident ' . ucfirst($gravite);
            $desc    = $incident->getDescription() ?? '';
            return [
                'titre'  => $titre,
                'texte'  => mb_strlen($desc) > 70 ? mb_substr($desc, 0, 67) . '...' : $desc,
                'gravite'=> $gravite,
            ];
        }, $incidentRepo->findActifs());

        // ── Grille planning ────────────────────────────────────────────────
        $formatH = static fn(\DateTimeInterface $dt): string =>
            $dt->format('G') . 'h' . ($dt->format('i') !== '00' ? $dt->format('i') : '');

        $intervenantsPlanning = [];
        $planningGrid         = [];

        foreach ($interventionsJour as $intervention) {
            $iv   = $intervention->getIntervenant();
            $ivId = $iv->getId();

            if (!array_key_exists($ivId, $intervenantsPlanning)) {
                $colorIdx  = count($intervenantsPlanning) % count(self::PALETTE);
                $nom       = $iv->getUtilisateur()->getNom();
                $parts     = array_filter(explode(' ', trim($nom)));
                $initiales = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
                $intervenantsPlanning[$ivId] = [
                    'id'        => $ivId,
                    'nom'       => $nom,
                    'prenom'    => array_values($parts)[0] ?? $nom,
                    'initiales' => mb_substr($initiales, 0, 2),
                    'color'     => self::PALETTE[$colorIdx],
                ];
            }

            $heure      = (int) $intervention->getDateDebut()->format('H');
            $nomPatient = $intervention->getBeneficiaire()->getUtilisateur()->getNom();
            $court      = mb_strlen($nomPatient) > 5 ? mb_substr($nomPatient, 0, 4) . '…' : $nomPatient;

            $planningGrid[$heure][$ivId][] = [
                'patient' => $court,
                'hdebut'  => $formatH($intervention->getDateDebut()),
                'hfin'    => $formatH($intervention->getDateFin()),
            ];
        }

        $dateLabel = self::JOURS[(int) $date->format('w')]
            . ' ' . $date->format('j')
            . ' ' . self::MOIS[(int) $date->format('n')]
            . ' ' . $date->format('Y');

        return $this->render('admin/dashboard.html.twig', [
            'prenom'               => $prenom,
            'dateLabel'            => $dateLabel,
            'prevDateStr'          => $prevDate->format('Y-m-d'),
            'nextDateStr'          => $nextDate->format('Y-m-d'),
            'stats'                => [
                ['icon' => 'users',     'label' => 'Intervenants',     'value' => $nbIntervenants,  'suffix' => 'actifs',          'sub' => null],
                ['icon' => 'heart',     'label' => 'Patients suivis',  'value' => $nbBeneficiaires, 'suffix' => 'dossiers actifs', 'sub' => null],
                ['icon' => 'clipboard', 'label' => 'Interventions',    'value' => $nbAujourdhui,    'suffix' => "aujourd'hui",     'sub' => 'dont ' . $nbTerminees . ' effectuées'],
            ],
            'alertes'              => $alertes,
            'patients'             => $patients,
            'soignants'            => $soignants,
            'occupiedByIv'         => $occupiedByIv,
            'todayStr'             => $date->format('Y-m-d'),
            'intervenantsPlanning' => $intervenantsPlanning,
            'planningGrid'         => $planningGrid,
        ]);
    }
}
