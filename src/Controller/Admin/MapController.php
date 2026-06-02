<?php

namespace App\Controller\Admin;

use App\Enum\StatutIntervention;
use App\Repository\BeneficiaireRepository;
use App\Repository\IntervenantRepository;
use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class MapController extends AbstractController
{
    use AdminTrait;

    private const PALETTE = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
    private const JOURS   = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    private const MOIS    = ['jan', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

    #[Route('/map', name: 'admin_map')]
    public function index(
        Request                $request,
        BeneficiaireRepository $beneficiaireRepo,
        IntervenantRepository  $intervenantRepo,
        InterventionRepository $interventionRepo,
    ): Response {
        $admin    = $this->getCurrentAdmin();
        $myIvIds  = array_map(fn($iv) => $iv->getId(), $intervenantRepo->findAllWithDetails($admin));

        $mode    = $request->query->get('mode', 'jour');
        $dateStr = $request->query->get('date', (new \DateTime('today'))->format('Y-m-d'));
        $date    = new \DateTime($dateStr);

        if ($mode === 'semaine') {
            $dow       = (int) $date->format('N');
            $weekStart = (clone $date)->modify('-' . ($dow - 1) . ' days midnight');
            $weekEnd   = (clone $weekStart)->modify('+6 days');
            $interventionsJour = $interventionRepo->findForWeek($weekStart, $myIvIds);
            $prevDate  = (clone $weekStart)->modify('-1 week');
            $nextDate  = (clone $weekStart)->modify('+1 week');
            $dateLabel = $weekStart->format('j') . ' ' . self::MOIS[(int)$weekStart->format('n') - 1]
                       . ' – ' . $weekEnd->format('j') . ' ' . self::MOIS[(int)$weekEnd->format('n') - 1]
                       . ' ' . $weekEnd->format('Y');
        } else {
            $interventionsJour = $interventionRepo->findForDate($date, $myIvIds);
            $prevDate  = (clone $date)->modify('-1 day');
            $nextDate  = (clone $date)->modify('+1 day');
            $dateLabel = self::JOURS[(int)$date->format('w')] . ' '
                       . $date->format('j') . ' ' . self::MOIS[(int)$date->format('n') - 1]
                       . ' ' . $date->format('Y');
        }

        $nbInterventions = count($interventionsJour);
        $nbTerminees     = count(array_filter(
            $interventionsJour,
            fn($i) => $i->getStatut() === StatutIntervention::Terminee
        ));

        $tousLesBens = $beneficiaireRepo->findAllForMap($admin);

        $patients = [];
        foreach ($tousLesBens as $ben) {
            if (!$ben->getLatitude() || !$ben->getLongitude()) continue;
            $u     = $ben->getUtilisateur();
            $nom   = $u->getNom();
            $parts = array_values(array_filter(explode(' ', trim($nom))));
            $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
            $patients[] = [
                'nom'       => $nom,
                'initiales' => mb_substr($init, 0, 2),
                'lat'       => (float) $ben->getLatitude(),
                'lng'       => (float) $ben->getLongitude(),
                'risque'    => $ben->getNiveauRisque(),
            ];
        }

        $soignantsMap = [];
        foreach ($interventionsJour as $idx => $interv) {
            $iv   = $interv->getIntervenant();
            $ivId = $iv->getId();
            if (!isset($soignantsMap[$ivId])) {
                $u     = $iv->getUtilisateur();
                $nom   = $u->getNom();
                $parts = array_values(array_filter(explode(' ', trim($nom))));
                $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
                $soignantsMap[$ivId] = [
                    'nom'      => $nom,
                    'initiales'=> mb_substr($init, 0, 2),
                    'color'    => self::PALETTE[$idx % count(self::PALETTE)],
                    'lats' => [], 'lngs' => [],
                ];
            }
            $ben = $interv->getBeneficiaire();
            if ($ben->getLatitude()) {
                $soignantsMap[$ivId]['lats'][] = (float) $ben->getLatitude();
                $soignantsMap[$ivId]['lngs'][] = (float) $ben->getLongitude();
            }
        }

        $routes   = [];
        $soignants = [];
        foreach ($soignantsMap as $data) {
            if (!empty($data['lats'])) {
                $clat = array_sum($data['lats']) / count($data['lats']) + 0.006;
                $clng = array_sum($data['lngs']) / count($data['lngs']) + 0.006;
                $pts  = [[$clat, $clng]];
                foreach ($data['lats'] as $i => $lat) {
                    $pts[] = [$lat, $data['lngs'][$i]];
                }
                $pts[] = [$clat, $clng];
                $routes[]    = ['color' => $data['color'], 'points' => $pts];
                $data['lat'] = $clat;
                $data['lng'] = $clng;
            } else {
                $data['lat'] = 44.8378;
                $data['lng'] = -0.5792;
            }
            unset($data['lats'], $data['lngs']);
            $soignants[] = $data;
        }

        $bensAvecInterv = array_unique(array_map(
            fn($i) => $i->getBeneficiaire()->getId(), $interventionsJour
        ));
        $nbHorsZone = count(array_filter(
            $tousLesBens,
            fn($b) => !in_array($b->getId(), $bensAvecInterv)
        ));

        $centerLat = !empty($patients) ? array_sum(array_column($patients, 'lat')) / count($patients) : 44.8378;
        $centerLng = !empty($patients) ? array_sum(array_column($patients, 'lng')) / count($patients) : -0.5792;

        return $this->render('admin/map.html.twig', [
            'patients'        => $patients,
            'soignants'       => $soignants,
            'routes'          => $routes,
            'nbInterventions' => $nbInterventions,
            'nbTerminees'     => $nbTerminees,
            'nbSoignants'     => count($soignants),
            'nbHorsZone'      => $nbHorsZone,
            'centerLat'       => $centerLat,
            'centerLng'       => $centerLng,
            'dateLabel'       => $dateLabel,
            'prevUrl'         => $this->generateUrl('admin_map', ['date' => $prevDate->format('Y-m-d'), 'mode' => $mode]),
            'nextUrl'         => $this->generateUrl('admin_map', ['date' => $nextDate->format('Y-m-d'), 'mode' => $mode]),
            'jourUrl'         => $this->generateUrl('admin_map', ['date' => $date->format('Y-m-d'), 'mode' => 'jour']),
            'semaineUrl'      => $this->generateUrl('admin_map', ['date' => $date->format('Y-m-d'), 'mode' => 'semaine']),
            'mode'            => $mode,
        ]);
    }
}
