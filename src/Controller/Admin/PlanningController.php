<?php

namespace App\Controller\Admin;

use App\Entity\Intervention;
use App\Entity\Planning;
use App\Enum\StatutIntervention;
use App\Enum\StatutPlanning;
use App\Enum\TypeIntervention;
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
class PlanningController extends AbstractController
{
    use AdminTrait;
    private const PALETTE   = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
    private const MOIS      = ['', 'jan', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
    private const JOURS     = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    private const JOURS_CT  = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

    #[Route('/planning', name: 'admin_planning')]
    public function index(
        Request                $request,
        IntervenantRepository  $intervenantRepo,
        InterventionRepository $interventionRepo,
        BeneficiaireRepository $benRepo,
    ): Response {
        $dateStr = $request->query->get('date');
        try {
            $date = $dateStr ? new \DateTime($dateStr) : new \DateTime('today');
        } catch (\Exception) {
            $date = new \DateTime('today');
        }
        $date->setTime(0, 0, 0);

        $prevDate  = (clone $date)->modify('-1 day');
        $nextDate  = (clone $date)->modify('+1 day');
        $dateLabel = self::JOURS[(int) $date->format('w')]
            . ' ' . $date->format('j')
            . ' ' . self::MOIS[(int) $date->format('n')]
            . ' ' . $date->format('Y');

        // Intervenants
        $admin        = $this->getCurrentAdmin();
        $intervenants = $intervenantRepo->findAllWithDetails($admin);
        $ivData = [];
        foreach ($intervenants as $idx => $iv) {
            $nom   = $iv->getUtilisateur()->getNom();
            $parts = array_values(array_filter(explode(' ', trim($nom))));
            $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
            $ivData[$iv->getId()] = [
                'id'        => $iv->getId(),
                'nom'       => $nom,
                'prenom'    => $parts[0] ?? $nom,
                'initiales' => mb_substr($init, 0, 2),
                'color'     => self::PALETTE[$idx % count(self::PALETTE)],
                'specialite'=> $iv->getSpecialite() ?? '',
                'disponible'=> $iv->isDisponibilite(),
                'statut'    => $iv->getStatut(),
            ];
        }

        $ivList  = array_values($ivData);
        $perSect = (int) ceil(count($ivList) / 3);
        $secteurs = [
            'Secteur A' => array_slice($ivList, 0, $perSect),
            'Secteur B' => array_slice($ivList, $perSect, $perSect),
            'Secteur C' => array_slice($ivList, $perSect * 2),
        ];

        // Grille du jour : grid[ivId][heure][] = bloc
        $grid = [];
        $nbInterventions = 0;
        $myIvIds = array_keys($ivData);
        foreach ($interventionRepo->findForDate($date, $myIvIds) as $interv) {
            $ivId   = $interv->getIntervenant()->getId();
            $h      = (int) $interv->getDateDebut()->format('H');
            $nomPat = $interv->getBeneficiaire()->getUtilisateur()->getNom();
            $court  = mb_strlen($nomPat) > 9 ? mb_substr($nomPat, 0, 8) . '…' : $nomPat;
            $grid[$ivId][$h][] = [
                'id'      => $interv->getId(),
                'patient' => $court,
                'hdebut'  => $interv->getDateDebut()->format('H:i'),
                'hfin'    => $interv->getDateFin()->format('H:i'),
                'type'    => ucfirst($interv->getTypeIntervention()->value),
            ];
            $nbInterventions++;
        }

        // Heures occupées par soignant (pour le blocage client)
        $occupiedByIv = [];
        foreach (array_keys($ivData) as $ivId) {
            $occupiedByIv[$ivId] = array_map('intval', array_keys($grid[$ivId] ?? []));
        }

        // Patients pour modal (requête légère, juste id + nom)
        $patients = [];
        foreach ($benRepo->findAllNames($admin) as $ben) {
            $patients[] = ['id' => $ben->getId(), 'nom' => $ben->getUtilisateur()->getNom()];
        }

        return $this->render('admin/planning.html.twig', [
            'date'           => $date->format('Y-m-d'),
            'dateLabel'      => $dateLabel,
            'prevDate'       => $prevDate->format('Y-m-d'),
            'nextDate'       => $nextDate->format('Y-m-d'),
            'isToday'        => $date->format('Y-m-d') === (new \DateTime('today'))->format('Y-m-d'),
            'intervenants'   => $ivData,
            'secteurs'       => $secteurs,
            'grid'           => $grid,
            'heures'         => range(7, 19),
            'nbInterventions'=> $nbInterventions,
            'patients'       => $patients,
            'soignants'      => $ivList,
            'occupiedByIv'   => $occupiedByIv,
        ]);
    }

    #[Route('/planning/intervention/{id}/reassigner', name: 'admin_planning_reassigner', methods: ['POST'])]
    public function reassigner(
        int                    $id,
        Request                $request,
        InterventionRepository $interventionRepo,
        IntervenantRepository  $intervenantRepo,
        EntityManagerInterface $em,
    ): Response {
        $interv  = $interventionRepo->find($id);
        $newIvId = (int) $request->request->get('iv_id');
        $dateStr = $request->request->get('date', (new \DateTime('today'))->format('Y-m-d'));

        if ($interv && $newIvId) {
            $newIv = $intervenantRepo->find($newIvId);
            if ($newIv) {
                if (!$newIv->isDisponibilite()) {
                    $this->addFlash('error', $newIv->getUtilisateur()->getNom() . ' n\'est pas disponible actuellement.');
                } elseif ($interventionRepo->isIntervenantBusy($newIv, $interv->getDateDebut(), $interv->getDateFin())) {
                    $this->addFlash('error', $newIv->getUtilisateur()->getNom() . ' est déjà occupé(e) sur ce créneau.');
                } else {
                    $interv->setIntervenant($newIv);
                    $em->flush();
                }
            }
        }

        return $this->redirectToRoute('admin_planning', ['date' => $dateStr]);
    }

    #[Route('/planning/intervention/{id}/annuler', name: 'admin_planning_annuler', methods: ['POST'])]
    public function annuler(
        int                    $id,
        Request                $request,
        InterventionRepository $interventionRepo,
        EntityManagerInterface $em,
    ): Response {
        $interv  = $interventionRepo->find($id);
        $dateStr = $request->request->get('date', (new \DateTime('today'))->format('Y-m-d'));
        if ($interv) {
            $em->remove($interv);
            $em->flush();
        }
        return $this->redirectToRoute('admin_planning', ['date' => $dateStr]);
    }

    #[Route('/planning/creer', name: 'admin_planning_creer', methods: ['POST'])]
    public function creer(
        Request                  $request,
        BeneficiaireRepository   $benRepo,
        IntervenantRepository    $intervenantRepo,
        InterventionRepository   $interventionRepo,
        PlanningRepository       $planningRepo,
        EntityManagerInterface   $em,
    ): Response {
        $benId   = (int) $request->request->get('ben_id');
        $ivId    = (int) $request->request->get('iv_id');
        $dateStr = $request->request->get('date', (new \DateTime('today'))->format('Y-m-d'));
        $hDebut  = (int) $request->request->get('h_debut', 9);
        $hFin    = (int) $request->request->get('h_fin', 10);
        $type    = $request->request->get('type', 'soins');

        $ben = $benRepo->find($benId);
        $iv  = $intervenantRepo->find($ivId);
        if (!$ben || !$iv || $hFin <= $hDebut) {
            return $this->redirectToRoute('admin_planning', ['date' => $dateStr]);
        }
        if (!$iv->isDisponibilite()) {
            $this->addFlash('error', $iv->getUtilisateur()->getNom() . ' n\'est pas disponible actuellement.');
            return $this->redirectToRoute('admin_planning', ['date' => $dateStr]);
        }

        $date      = new \DateTime($dateStr);
        $dateDebut = (clone $date)->setTime($hDebut, 0);
        $dateFin   = (clone $date)->setTime($hFin, 0);

        // Bloquer si le soignant est déjà occupé sur ce créneau
        if ($interventionRepo->isIntervenantBusy($iv, $dateDebut, $dateFin)) {
            $this->addFlash('error', $iv->getUtilisateur()->getNom() . ' est déjà occupé(e) sur ce créneau.');
            return $this->redirectToRoute('admin_planning', ['date' => $dateStr]);
        }

        $dow       = (int) $date->format('N');
        $weekStart = (clone $date)->modify('-' . ($dow - 1) . ' days midnight');
        $planning  = $planningRepo->findOneBy(['semaine' => $weekStart]);

        if (!$planning) {
            $admin    = $this->getCurrentAdmin();
            $planning = new Planning();
            $planning->setAdmin($admin);
            $planning->setSemaine($weekStart);
            $planning->setStatut(StatutPlanning::Publie);
            $em->persist($planning);
        }

        $interv = new Intervention();
        $interv->setBeneficiaire($ben);
        $interv->setIntervenant($iv);
        $interv->setPlanning($planning);
        $interv->setDateDebut($dateDebut);
        $interv->setDateFin($dateFin);
        $interv->setStatut(StatutIntervention::Planifiee);
        $interv->setTypeIntervention(TypeIntervention::from($type));
        $em->persist($interv);
        $em->flush();

        return $this->redirectToRoute('admin_planning', ['date' => $dateStr]);
    }
}
