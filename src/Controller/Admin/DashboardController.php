<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'prenom' => 'Claudine',
            'stats' => [
                [
                    'label' => 'Intervenants actifs',
                    'value' => 55,
                    'suffix' => 'ce mois.',
                    'sub' => null,
                ],
                [
                    'label' => 'Patients suivis',
                    'value' => 150,
                    'suffix' => 'dossiers actifs',
                    'sub' => null,
                ],
                [
                    'label' => 'Interventions',
                    'value' => 18,
                    'suffix' => "aujourd'hui",
                    'sub' => 'dont 14 effectuées',
                ],
            ],
            'alertes' => [
                [
                    'titre' => 'Patient non assigné',
                    'texte' => 'Aucun soignant assignée pour le créneau 14-15h ...',
                ],
                [
                    'titre' => 'Arrêt maladie signalé',
                    'texte' => 'Marc Dubois a signalé un arrêt maladie. 3 Patient...',
                ],
                [
                    'titre' => 'Incident signalé',
                    'texte' => 'Incident signalé chez Marcel Neveu lors de...',
                ],
                [
                    'titre' => 'Patient non assigné...',
                    'texte' => "Fatima Musson n'as toujours pas de soigna...",
                ],
            ],
        ]);
    }
}
