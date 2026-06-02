<?php

namespace App\Controller\Admin;

use App\Entity\Beneficiaire;
use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use App\Repository\BeneficiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class PatientsController extends AbstractController
{
    use AdminTrait;
    private const PALETTE = ['#8b5cf6', '#2dd4bf', '#ec4899', '#10b981', '#06b6d4', '#f59e0b'];

    private const TYPE_LABELS = [
        'menage'         => 'Ménage',
        'toilette'       => 'Toilette',
        'repas'          => 'Repas',
        'accompagnement' => 'Accompagnement',
        'surveillance'   => 'Surveillance',
        'soins'          => 'Soins',
    ];

    private const RISQUE_LABELS = [
        'modere'   => 'Modéré',
        'eleve'    => 'Élevé',
        'critique' => 'Critique',
        'faible'   => 'Faible',
    ];

    #[Route('/patients', name: 'admin_patients')]
    public function index(BeneficiaireRepository $repo): Response
    {
        $beneficiaires = $repo->findAllWithDetails($this->getCurrentAdmin());
        $today         = new \DateTime('today');
        $yesterday     = (clone $today)->modify('-1 day');
        $joursAbr      = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

        $formatDate = function (\DateTimeInterface $dt) use ($today, $yesterday, $joursAbr): string {
            $h = $dt->format('G') . 'h' . ($dt->format('i') !== '00' ? $dt->format('i') : '');
            if ($dt->format('Y-m-d') === $today->format('Y-m-d')) {
                return "Aujourd'hui {$h}";
            }
            if ($dt->format('Y-m-d') === $yesterday->format('Y-m-d')) {
                return "Hier {$h}";
            }
            return $joursAbr[(int) $dt->format('w')] . ". {$h}";
        };

        $initiales = static function (string $nom): string {
            $parts = array_filter(explode(' ', trim($nom)));
            return mb_strtoupper(mb_substr(
                implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)),
                0, 2
            ));
        };

        $patients = [];
        foreach ($beneficiaires as $idx => $b) {
            $nom      = $b->getUtilisateur()->getNom();
            $age      = $b->getDateNaissance()->diff($today)->y;
            $adrParts = explode(',', $b->getAdresse());
            $ville    = trim(end($adrParts));
            $risque   = $b->getNiveauRisque() ?? 'modere';

            $crs          = [];
            $soignantsMap = [];
            foreach ($b->getInterventions() as $intervention) {
                $iv    = $intervention->getIntervenant();
                $ivId  = $iv->getId();
                $ivNom = $iv->getUtilisateur()->getNom();

                if (!isset($soignantsMap[$ivId])) {
                    $soignantsMap[$ivId] = [
                        'nom'        => $ivNom,
                        'initiales'  => $initiales($ivNom),
                        'specialite' => $iv->getSpecialite(),
                    ];
                }

                $cr = $intervention->getCompteRendu();
                if ($cr !== null) {
                    $typeVal = $intervention->getTypeIntervention()?->value ?? '';
                    $crs[]   = [
                        'titre'             => self::TYPE_LABELS[$typeVal] ?? 'Intervention',
                        'dateLabel'         => $formatDate($cr->getDateRedaction()),
                        'contenu'           => $cr->getContenu(),
                        'soignant'          => $ivNom,
                        'soignantInitiales' => $initiales($ivNom),
                        'valide'            => $cr->isValide(),
                    ];
                }
            }

            $patients[] = [
                'id'            => $b->getId(),
                'nom'           => $nom,
                'email'         => $b->getUtilisateur()->getEmail(),
                'initiales'     => $initiales($nom),
                'age'           => $age,
                'adresse'       => $b->getAdresse(),
                'ville'         => $ville,
                'risque'        => $risque,
                'risqueLabel'   => self::RISQUE_LABELS[$risque] ?? ucfirst($risque),
                'pathologie'    => $b->getPathologie() ?? '',
                'rdvParSemaine' => $b->getRdvParSemaine(),
                'dateNaissance' => $b->getDateNaissance()->format('Y-m-d'),
                'color'         => self::PALETTE[$idx % count(self::PALETTE)],
                'crs'           => $crs,
                'nbCrs'         => count($crs),
                'soignants'     => array_values($soignantsMap),
            ];
        }

        return $this->render('admin/patients.html.twig', [
            'patients'   => $patients,
            'nbPatients' => count($patients),
        ]);
    }

    #[Route('/patients/new', name: 'admin_patients_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if (!$this->isCsrfTokenValid('new_patient', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setEmail($request->request->get('email'));
        $utilisateur->setRole(RoleUtilisateur::Beneficiaire);
        $utilisateur->setMdp($hasher->hashPassword($utilisateur, $request->request->get('motDePasse')));

        $beneficiaire = new Beneficiaire();
        $beneficiaire->setUtilisateur($utilisateur);
        $beneficiaire->setAdminCreateur($this->getCurrentAdmin());
        $beneficiaire->setDateNaissance(new \DateTime($request->request->get('dateNaissance')));
        $beneficiaire->setAdresse($request->request->get('adresse'));
        $beneficiaire->setPathologie($request->request->get('pathologie') ?: null);
        $beneficiaire->setNiveauRisque($request->request->get('niveauRisque', 'modere'));
        $rdv = $request->request->get('rdvParSemaine');
        if ($rdv !== null && $rdv !== '') $beneficiaire->setRdvParSemaine((int) $rdv);
        $beneficiaire->setLatitude('0');
        $beneficiaire->setLongitude('0');

        $em->persist($utilisateur);
        $em->persist($beneficiaire);
        $em->flush();

        return $this->redirectToRoute('admin_patients');
    }

    #[Route('/patients/{id}/edit', name: 'admin_patients_edit', methods: ['POST'])]
    public function edit(
        int $id,
        Request $request,
        BeneficiaireRepository $repo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if (!$this->isCsrfTokenValid('edit_patient_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $beneficiaire = $repo->find($id);
        if (!$beneficiaire) {
            throw $this->createNotFoundException();
        }

        $utilisateur = $beneficiaire->getUtilisateur();
        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setEmail($request->request->get('email'));

        $newPassword = $request->request->get('motDePasse');
        if ($newPassword !== '') {
            $utilisateur->setMdp($hasher->hashPassword($utilisateur, $newPassword));
        }

        $beneficiaire->setDateNaissance(new \DateTime($request->request->get('dateNaissance')));
        $beneficiaire->setAdresse($request->request->get('adresse'));
        $beneficiaire->setPathologie($request->request->get('pathologie') ?: null);
        $beneficiaire->setNiveauRisque($request->request->get('niveauRisque', 'modere'));
        $rdv = $request->request->get('rdvParSemaine');
        if ($rdv !== null && $rdv !== '') $beneficiaire->setRdvParSemaine((int) $rdv);

        $em->flush();

        return $this->redirectToRoute('admin_patients');
    }

    #[Route('/patients/{id}/delete', name: 'admin_patients_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        BeneficiaireRepository $repo,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('delete_patient_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $beneficiaire = $repo->find($id);
        if (!$beneficiaire) {
            throw $this->createNotFoundException();
        }

        $utilisateur = $beneficiaire->getUtilisateur();

        // Delete interventions and their dependencies before removing the beneficiaire (FK constraints)
        foreach ($beneficiaire->getInterventions() as $intervention) {
            foreach ($intervention->getIncidents() as $incident) {
                $em->remove($incident);
            }
            if ($cr = $intervention->getCompteRendu()) {
                foreach ($cr->getNotifications() as $notif) {
                    $em->remove($notif);
                }
            }
            $em->remove($intervention); // cascade: remove handles CompteRendu
        }
        $em->flush();

        // Clean up utilisateur-level relations before removing the user
        foreach ($utilisateur->getNotifications() as $notif) {
            $em->remove($notif);
        }
        foreach ($utilisateur->getMessagesEnvoyes() as $msg) {
            $em->remove($msg);
        }
        foreach ($utilisateur->getMessagesRecus() as $msg) {
            $em->remove($msg);
        }

        // Removing utilisateur cascades to beneficiaire (and its aidants)
        $em->remove($utilisateur);
        $em->flush();

        return $this->redirectToRoute('admin_patients');
    }
}
