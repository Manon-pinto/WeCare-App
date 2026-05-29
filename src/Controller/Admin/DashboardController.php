<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
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
    private const JOURS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    private const MOIS  = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
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
        $user       = $this->getUser();
        $nomComplet = $user?->getNom() ?? '';
        $prenom     = explode(' ', trim($nomComplet))[0] ?? $nomComplet;

        // ── Date de navigation ─────────────────────────────────────────────
        $dateStr = $request->query->get('date', 'today');
        try {
            $date = new \DateTime($dateStr);
        } catch (\Exception) {
            $date = new \DateTime('today');
        }
        $date->setTime(0, 0, 0);
        $prevDate = (clone $date)->modify('-1 day');
        $nextDate = (clone $date)->modify('+1 day');

        // ── Stats ──────────────────────────────────────────────────────────
        $nbIntervenants  = $intervenantRepo->countActifsCeMois();
        $nbBeneficiaires = $beneficiaireRepo->countTotal();
        $nbAujourdhui    = $interventionRepo->countAujourdhui();
        $nbTerminees     = $interventionRepo->countTermineesAujourdhui();

        $incidents = $incidentRepo->findActifs();
        $alertes   = array_map(static function ($incident) {
            $gravite = $incident->getGravite()?->value ?? 'moderee';
            $zone    = $incident->getZone();
            $titre   = $zone ? 'Incident — ' . $zone : 'Incident ' . ucfirst($gravite);
            $desc    = $incident->getDescription() ?? '';
            $texte   = mb_strlen($desc) > 70 ? mb_substr($desc, 0, 67) . '...' : $desc;
            return ['titre' => $titre, 'texte' => $texte, 'gravite' => $gravite];
        }, $incidents);

        // ── Planning ───────────────────────────────────────────────────────
        $interventionsJour = $interventionRepo->findForDate($date);

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
            $court      = mb_strlen($nomPatient) > 5 ? mb_substr($nomPatient, 0, 4) . '...' : $nomPatient;

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
                ['icon' => 'users',     'label' => 'Intervenants actifs', 'value' => $nbIntervenants,  'suffix' => 'ce mois.',        'sub' => null],
                ['icon' => 'heart',     'label' => 'Patients suivis',     'value' => $nbBeneficiaires, 'suffix' => 'dossiers actifs', 'sub' => null],
                ['icon' => 'clipboard', 'label' => 'Interventions',       'value' => $nbAujourdhui,    'suffix' => "aujourd'hui",     'sub' => 'dont ' . $nbTerminees . ' effectuées'],
            ],
            'alertes'              => $alertes,
            'intervenantsPlanning' => $intervenantsPlanning,
            'planningGrid'         => $planningGrid,
        ]);
    }
}
