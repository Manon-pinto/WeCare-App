<?php

namespace App\Controller\Intervenant;

use App\Repository\InterventionRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intervenant', name: 'intervenant_')]
class PageController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        InterventionRepository $interventionRepo,
        NotificationRepository $notificationRepo,
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $interventions = $intervenant
            ? $interventionRepo->findByIntervenantAndDateRange($intervenant, $today, $tomorrow)
            : [];

        $pointsAttention = $intervenant
            ? $interventionRepo->findPatientsWithAlerts($intervenant)
            : [];

        $nonLues = $notificationRepo->countUnreadByUser($user);

        // Dédupliquer patients du jour
        $seen = [];
        $patients = [];
        foreach ($interventions as $i) {
            $bid = $i->getBeneficiaire()->getId();
            if (!in_array($bid, $seen)) {
                $seen[] = $bid;
                $patients[] = $i;
            }
        }

        $prochaine = null;
        foreach ($interventions as $i) {
            if ($i->getStatut()?->value === 'planifiee') {
                $prochaine = $i;
                break;
            }
        }

        $effectuees = count(array_filter($interventions, fn($i) => $i->getStatut()?->value === 'terminee'));
        $derniere = count($interventions) ? $interventions[count($interventions) - 1] : null;

        $crARediger = $intervenant
            ? $interventionRepo->countSansCompteRenduByIntervenant($intervenant)
            : 0;

        return $this->render('intervenant/dashboard.html.twig', [
            'nom' => $user->getNom(),
            'interventions' => $interventions,
            'prochaine' => $prochaine,
            'pointsAttention' => $pointsAttention,
            'patients' => $patients,
            'effectuees' => $effectuees,
            'total' => count($interventions),
            'derniere' => $derniere,
            'nonLues' => $nonLues,
            'crARediger' => $crARediger,
        ]);
    }

    #[Route('/planning', name: 'planning')]
    public function planning(Request $request, InterventionRepository $interventionRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        $offset = (int) $request->query->get('offset', 0);
        $dateDebut = new \DateTime('monday this week');
        $dateDebut->setTime(0, 0);
        $dateDebut->modify(($offset >= 0 ? '+' : '') . ($offset * 7) . ' days');
        $dateFin = (clone $dateDebut)->modify('+6 days')->setTime(23, 59, 59);

        $interventions = $intervenant
            ? $interventionRepo->findByIntervenantAndDateRange($intervenant, $dateDebut, $dateFin)
            : [];

        $jours = [];
        $cursor = clone $dateDebut;
        for ($i = 0; $i < 7; $i++) {
            $key = $cursor->format('Y-m-d');
            $jours[$key] = ['date' => $cursor->format('Y-m-d'), 'interventions' => []];
            $cursor->modify('+1 day');
        }
        foreach ($interventions as $intervention) {
            $key = $intervention->getDateDebut()->format('Y-m-d');
            if (isset($jours[$key])) {
                $jours[$key]['interventions'][] = $intervention;
            }
        }

        return $this->render('intervenant/planning.html.twig', [
            'jours' => array_values($jours),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'offset' => $offset,
        ]);
    }

    #[Route('/patients', name: 'patients')]
    public function patients(): Response
    {
        return $this->render('intervenant/patients.html.twig');
    }

    #[Route('/patients/{id}', name: 'patient_detail')]
    public function patientDetail(int $id): Response
    {
        return $this->render('intervenant/patient_detail.html.twig', ['patientId' => $id]);
    }

    #[Route('/comptes-rendus', name: 'comptes_rendus')]
    public function comptesRendus(InterventionRepository $interventionRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        $interventions = $intervenant
            ? $interventionRepo->findWithCompteRenduByIntervenant($intervenant)
            : [];

        return $this->render('intervenant/comptes_rendus.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route('/incidents', name: 'incidents')]
    public function incidents(): Response
    {
        return $this->render('intervenant/incidents.html.twig');
    }

    #[Route('/profil', name: 'profil')]
    public function profil(): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        return $this->render('intervenant/profil.html.twig', [
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
            'specialite' => $intervenant?->getSpecialite(),
            'telephone' => $intervenant?->getTelephone(),
            'disponibilite' => $intervenant?->isDisponibilite(),
            'rayonIntervention' => $intervenant?->getRayonIntervention(),
            'vehicule' => $intervenant?->getVehicule(),
            'membreDepuis' => $intervenant?->getCreatedAt()?->format('d/m/Y'),
        ]);
    }

    #[Route('/notifications', name: 'notifications')]
    public function notifications(NotificationRepository $notificationRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $notifications = $notificationRepo->findByUser($user);

        return $this->render('intervenant/notifications.html.twig', [
            'notifications' => $notifications,
            'nonLues' => $notificationRepo->countUnreadByUser($user),
        ]);
    }

    #[Route('/map', name: 'map')]
    public function map(InterventionRepository $interventionRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $intervenant = $user->getIntervenant();

        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $interventions = $intervenant
            ? $interventionRepo->findByIntervenantAndDateRange($intervenant, $today, $tomorrow)
            : [];

        return $this->render('intervenant/map.html.twig', [
            'interventions' => $interventions,
        ]);
    }
}
