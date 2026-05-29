<?php

namespace App\Controller\Admin;

use App\Repository\AidantRepository;
use App\Repository\IntervenantRepository;
use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class PrisesEnChargeController extends AbstractController
{
    private const PALETTE = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];

    #[Route('/prises-en-charge', name: 'admin_prises_en_charge')]
    public function index(
        AidantRepository      $aidantRepo,
        IntervenantRepository $intervenantRepo,
        InterventionRepository $interventionRepo,
    ): Response {
        $today = new \DateTime('today');

        // Interventions du jour pour calculer la dispo des soignants
        $todayInterventions = $interventionRepo->findForDate($today);
        $soignantsOccupe = [];
        foreach ($todayInterventions as $interv) {
            $ivId = $interv->getIntervenant()->getId();
            $h    = (int) $interv->getDateDebut()->format('H');
            $hFin = (int) $interv->getDateFin()->format('H');
            for ($hh = $h; $hh < $hFin; $hh++) {
                $soignantsOccupe[$ivId][$hh] = true;
            }
        }

        // Lignes de prises en charge
        $aidants = $aidantRepo->findAllWithDetails();
        $rows    = [];
        foreach ($aidants as $aidant) {
            $ben = $aidant->getBeneficiaire();
            $age = (int) (new \DateTime())->diff($ben->getDateNaissance())->y;

            $soignants   = [];
            $hasUpcoming = false;
            foreach ($ben->getInterventions() as $interv) {
                $iv   = $interv->getIntervenant();
                $ivId = $iv->getId();
                if (!isset($soignants[$ivId])) {
                    $nom   = $iv->getUtilisateur()->getNom();
                    $parts = array_values(array_filter(explode(' ', trim($nom))));
                    $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
                    $soignants[$ivId] = ['nom' => $nom, 'initiales' => mb_substr($init, 0, 2)];
                }
                if ($interv->getDateDebut() >= $today) {
                    $hasUpcoming = true;
                }
            }

            $rows[] = [
                'id'          => $aidant->getId(),
                'benId'       => $ben->getId(),
                'aidantNom'   => $aidant->getNomComplet(),
                'telephone'   => $aidant->getTelephone() ?? '',
                'email'       => $aidant->getEmail() ?? '',
                'lien'        => $aidant->getLien() ?? '',
                'patientNom'  => $ben->getUtilisateur()->getNom(),
                'patientAge'  => $age,
                'adresse'     => $ben->getAdresse() ?? '',
                'niveau'      => $ben->getNiveauRisque() ?? 'faible',
                'actif'       => $hasUpcoming,
                'soignants'   => array_values($soignants),
            ];
        }

        // Données soignants pour le modal d'assignation
        $intervenants = $intervenantRepo->findAllWithDetails();
        $soignantsJson = [];
        foreach ($intervenants as $idx => $iv) {
            $nom   = $iv->getUtilisateur()->getNom();
            $parts = array_values(array_filter(explode(' ', trim($nom))));
            $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));
            $ivId  = $iv->getId();
            $soignantsJson[] = [
                'id'            => $ivId,
                'nom'           => $nom,
                'initiales'     => mb_substr($init, 0, 2),
                'color'         => self::PALETTE[$idx % count(self::PALETTE)],
                'specialite'    => $iv->getSpecialite() ?? 'Aide-soignant(e)',
                'rayon'         => (int) ($iv->getRayonIntervention() ?? 5),
                'occupiedHours' => array_keys($soignantsOccupe[$ivId] ?? []),
            ];
        }

        return $this->render('admin/prises_en_charge.html.twig', [
            'rows'         => $rows,
            'soignantsJson'=> $soignantsJson,
        ]);
    }
}
