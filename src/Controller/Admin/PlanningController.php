<?php

namespace App\Controller\Admin;

use App\Repository\IntervenantRepository;
use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class PlanningController extends AbstractController
{
    private const PALETTE = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
    private const MOIS    = ['', 'jan', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
    private const JOURS_COURTS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    #[Route('/planning', name: 'admin_planning')]
    public function index(
        Request               $request,
        IntervenantRepository $intervenantRepo,
        InterventionRepository $interventionRepo,
    ): Response {
        // Calcul de la semaine
        $weekStr = $request->query->get('week');
        try {
            $weekStart = $weekStr ? new \DateTime($weekStr) : new \DateTime('monday this week');
            if ($weekStr) {
                $weekStart->modify('monday this week');
            }
        } catch (\Exception) {
            $weekStart = new \DateTime('monday this week');
        }
        $weekStart->setTime(0, 0, 0);

        $prevWeek = (clone $weekStart)->modify('-7 days');
        $nextWeek = (clone $weekStart)->modify('+7 days');
        $weekEnd  = (clone $weekStart)->modify('+6 days')->setTime(23, 59, 59);

        $dateLabel = $weekStart->format('j') . ' ' . self::MOIS[(int) $weekStart->format('n')]
            . ' — ' . $weekEnd->format('j') . ' ' . self::MOIS[(int) $weekEnd->format('n')]
            . ' ' . $weekStart->format('Y');

        // Jours de la semaine
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $d = (clone $weekStart)->modify("+$i days");
            $weekDays[] = [
                'label' => self::JOURS_COURTS[$i],
                'num'   => (int) $d->format('j'),
                'month' => self::MOIS[(int) $d->format('n')],
                'iso'   => $d->format('Y-m-d'),
            ];
        }

        // Intervenants
        $intervenants = $intervenantRepo->findAllWithDetails();
        $ivData = [];
        foreach ($intervenants as $idx => $iv) {
            $u    = $iv->getUtilisateur();
            $nom  = $u->getNom();
            $parts = array_values(array_filter(explode(' ', trim($nom))));
            $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
            $ivData[$iv->getId()] = [
                'id'        => $iv->getId(),
                'nom'       => $nom,
                'prenom'    => $parts[0] ?? $nom,
                'initiales' => mb_substr($init, 0, 2),
                'color'     => self::PALETTE[$idx % count(self::PALETTE)],
                'specialite'=> $iv->getSpecialite() ?? '',
            ];
        }

        // Répartition en secteurs
        $ivList  = array_values($ivData);
        $perSect = (int) ceil(count($ivList) / 3);
        $secteurs = [
            'Secteur A' => array_slice($ivList, 0, $perSect),
            'Secteur B' => array_slice($ivList, $perSect, $perSect),
            'Secteur C' => array_slice($ivList, $perSect * 2),
        ];

        // Grille : grid[dayIndex][ivId][heure][] = bloc
        $formatH = static fn(\DateTimeInterface $dt): string =>
            $dt->format('G') . 'h' . ($dt->format('i') !== '00' ? $dt->format('i') : '');

        $grid = [];
        foreach ($interventionRepo->findForWeek($weekStart) as $interv) {
            $iv   = $interv->getIntervenant();
            $ivId = $iv->getId();
            $dd   = $interv->getDateDebut();
            $day  = ((int) $dd->format('N')) - 1; // 0 = Lundi
            $h    = (int) $dd->format('H');
            $nomPat = $interv->getBeneficiaire()->getUtilisateur()->getNom();
            $court  = mb_strlen($nomPat) > 6 ? mb_substr($nomPat, 0, 5) . '…' : $nomPat;

            $grid[$day][$ivId][$h][] = [
                'patient' => $court,
                'hdebut'  => $formatH($interv->getDateDebut()),
                'hfin'    => $formatH($interv->getDateFin()),
            ];
        }

        return $this->render('admin/planning.html.twig', [
            'weekDays'   => $weekDays,
            'dateLabel'  => $dateLabel,
            'prevWeek'   => $prevWeek->format('Y-m-d'),
            'nextWeek'   => $nextWeek->format('Y-m-d'),
            'intervenants' => $ivData,
            'secteurs'   => $secteurs,
            'grid'       => $grid,
            'heures'     => range(7, 17),
        ]);
    }
}
