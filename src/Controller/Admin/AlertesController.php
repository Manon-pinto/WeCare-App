<?php

namespace App\Controller\Admin;

use App\Repository\IncidentRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AlertesController extends AbstractController
{
    private const GRAVITE_LABELS = [
        'faible'   => 'Faible',
        'moderee'  => 'Modérée',
        'grave'    => 'Grave',
        'critique' => 'Critique',
    ];

    private const STATUT_LABELS = [
        'signale'        => 'Signalé',
        'en_traitement'  => 'En traitement',
        'resolu'         => 'Résolu',
        'cloture'        => 'Clôturé',
    ];

    private const NOTIF_LABELS = [
        'compte_rendu_soumis' => 'CR soumis',
        'compte_rendu_valide' => 'CR validé',
        'incident_signale'    => 'Incident signalé',
        'rappel'              => 'Rappel',
    ];

    #[Route('/notifications', name: 'admin_notifications')]
    public function index(
        IncidentRepository     $incidentRepo,
        NotificationRepository $notifRepo
    ): Response {
        $incidents     = $incidentRepo->findAllWithDetails();
        $notifications = $notifRepo->findAllWithUser();

        $incidentsData = [];
        foreach ($incidents as $inc) {
            $iv  = $inc->getIntervention()->getIntervenant();
            $ben = $inc->getIntervention()->getBeneficiaire();

            $gravite = $inc->getGravite()?->value ?? 'moderee';
            $statut  = $inc->getStatut()?->value  ?? 'signale';
            $zone    = $inc->getZone();
            $titre   = $zone
                ? 'Incident — ' . $zone
                : 'Incident ' . (self::GRAVITE_LABELS[$gravite] ?? ucfirst($gravite));

            $incidentsData[] = [
                'id'           => $inc->getId(),
                'titre'        => $titre,
                'description'  => $inc->getDescription() ?? '',
                'gravite'      => $gravite,
                'graviteLabel' => self::GRAVITE_LABELS[$gravite] ?? ucfirst($gravite),
                'statut'       => $statut,
                'statutLabel'  => self::STATUT_LABELS[$statut] ?? ucfirst($statut),
                'zone'         => $zone,
                'date'         => $inc->getDateSignalement(),
                'patient'      => $ben->getUtilisateur()->getNom(),
                'intervenant'  => $iv->getUtilisateur()->getNom(),
            ];
        }

        $notifsData = [];
        foreach ($notifications as $notif) {
            $typeVal = $notif->getType()?->value ?? 'rappel';
            $notifsData[] = [
                'id'      => $notif->getId(),
                'type'    => $typeVal,
                'label'   => self::NOTIF_LABELS[$typeVal] ?? ucfirst($typeVal),
                'message' => $notif->getMessage() ?? '',
                'lu'      => $notif->isLu(),
                'date'    => $notif->getCreatedAt(),
                'user'    => $notif->getUtilisateur()->getNom(),
            ];
        }

        // Comptes par statut pour les stats
        $counts = ['signale' => 0, 'en_traitement' => 0, 'resolu' => 0, 'cloture' => 0];
        foreach ($incidentsData as $i) {
            if (isset($counts[$i['statut']])) {
                $counts[$i['statut']]++;
            }
        }

        return $this->render('admin/alertes.html.twig', [
            'incidents'     => $incidentsData,
            'notifications' => $notifsData,
            'nbIncidents'   => count($incidentsData),
            'nbNotifs'      => count($notifsData),
            'nbNonLus'      => count(array_filter($notifsData, fn($n) => !$n['lu'])),
            'counts'        => $counts,
        ]);
    }
}
