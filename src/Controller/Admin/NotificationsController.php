<?php

namespace App\Controller\Admin;

use App\Repository\IncidentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class NotificationsController extends AbstractController
{
    #[Route('/notifications', name: 'admin_notifications')]
    public function index(
        Request            $request,
        IncidentRepository $incidentRepo,
    ): Response {
        $incidents = $incidentRepo->findAllWithDetails();

        $alertes = array_map(static function ($inc) {
            $gravite = $inc->getGravite()?->value ?? 'moderee';
            $iv      = $inc->getIntervention()->getIntervenant()->getUtilisateur()->getNom();
            $ben     = $inc->getIntervention()->getBeneficiaire()->getUtilisateur()->getNom();
            $zone    = $inc->getZone();
            $desc    = $inc->getDescription() ?? '';
            $texte   = mb_strlen($desc) > 85 ? mb_substr($desc, 0, 82) . '…' : $desc;

            $diff = max(0, (new \DateTime())->getTimestamp() - $inc->getDateSignalement()->getTimestamp());
            if ($diff < 3600)       $ago = 'Il y a ' . max(1, (int) ($diff / 60)) . ' min';
            elseif ($diff < 86400)  $ago = 'Il y a ' . (int) ($diff / 3600) . 'h';
            else                    $ago = 'Il y a ' . (int) ($diff / 86400) . 'j';

            return [
                'id'         => $inc->getId(),
                'gravite'    => $gravite,
                'titre'      => 'Incident signalé',
                'texte'      => $texte,
                'timeAgo'    => $ago,
                'signalePar' => $iv,
                'patient'    => $ben,
                'date'       => $inc->getDateSignalement()->format('d/m/Y'),
                'heure'      => $inc->getDateSignalement()->format('H\hi'),
                'zone'       => $zone ?? ucfirst($gravite),
                'description'=> $desc,
            ];
        }, $incidents);

        $selectedId = (int) $request->query->get('id', $alertes[0]['id'] ?? 0);
        $selected   = null;
        foreach ($alertes as $a) {
            if ($a['id'] === $selectedId) { $selected = $a; break; }
        }

        return $this->render('admin/notifications.html.twig', [
            'alertes'  => $alertes,
            'selected' => $selected ?? ($alertes[0] ?? null),
        ]);
    }
}
