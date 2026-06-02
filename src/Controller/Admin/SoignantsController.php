<?php

namespace App\Controller\Admin;

use App\Entity\Intervenant;
use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use App\Repository\IntervenantRepository;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class SoignantsController extends AbstractController
{
    use AdminTrait;
    private const PALETTE  = ['#06b6d4', '#8b5cf6', '#ec4899', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
    private const SECTEURS = ['A', 'A', 'A', 'A', 'B', 'B', 'B', 'C', 'C', 'C'];

    #[Route('/soignants', name: 'admin_soignants')]
    public function index(
        Request               $request,
        IntervenantRepository $intervenantRepo,
        InterventionRepository $interventionRepo,
    ): Response {
        $admin        = $this->getCurrentAdmin();
        $intervenants = $intervenantRepo->findAllWithDetails($admin);

        $weekStart = new \DateTime('monday this week midnight');
        $joursLabels = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];

        $allWeekIvs = $interventionRepo->findForWeek($weekStart, array_map(fn($iv) => $iv->getId(), $intervenants));
        $weekByIv   = [];
        foreach ($allWeekIvs as $wi) {
            $weekByIv[$wi->getIntervenant()->getId()][] = $wi;
        }

        $soignants = [];
        foreach ($intervenants as $idx => $iv) {
            $u     = $iv->getUtilisateur();
            $nom   = $u->getNom();
            $parts = array_values(array_filter(explode(' ', trim($nom))));
            $init  = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), $parts)));

            $ivWeekIvs = $weekByIv[$iv->getId()] ?? [];
            $weekGrid  = array_fill(0, 7, 0);
            foreach ($ivWeekIvs as $wi) {
                $dow = ((int) $wi->getDateDebut()->format('N')) - 1;
                if ($dow >= 0 && $dow < 7) $weekGrid[$dow]++;
            }

            $soignants[] = [
                'id'         => $iv->getId(),
                'nom'        => $nom,
                'prenom'     => $parts[0] ?? '',
                'nomFamille' => $parts[1] ?? '',
                'initiales'  => mb_substr($init, 0, 2),
                'color'      => self::PALETTE[$idx % count(self::PALETTE)],
                'specialite' => $iv->getSpecialite() ?? 'Infirmier(e)',
                'secteur'    => self::SECTEURS[$idx] ?? 'A',
                'telephone'  => $iv->getTelephone() ?? '',
                'email'      => $u->getEmail() ?? '',
                'disponible' => $iv->isDisponibilite(),
            'statut'     => $iv->getStatut(),
                'rayon'      => (int) ($iv->getRayonIntervention() ?? 5),
                'weekCount'  => count($ivWeekIvs),
                'weekGrid'   => $weekGrid,
                'jours'      => $joursLabels,
            ];
        }

        $selectedId = (int) $request->query->get('id', $soignants[0]['id'] ?? 0);
        $selected   = null;
        foreach ($soignants as $s) {
            if ($s['id'] === $selectedId) { $selected = $s; break; }
        }

        return $this->render('admin/soignants.html.twig', [
            'soignants' => $soignants,
            'selected'  => $selected ?? ($soignants[0] ?? null),
        ]);
    }

    #[Route('/soignants/ajouter', name: 'admin_soignants_ajouter', methods: ['POST'])]
    public function ajouter(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $prenom     = trim($request->request->get('prenom', ''));
        $nomFam     = trim($request->request->get('nom_famille', ''));
        $email      = trim($request->request->get('email', ''));
        $telephone  = trim($request->request->get('telephone', ''));
        $specialite = trim($request->request->get('specialite', 'Infirmier(e)'));
        $rayon      = (float) $request->request->get('rayon', 10);

        $u = new Utilisateur();
        $u->setNom(trim($prenom . ' ' . $nomFam));
        $u->setEmail($email);
        $u->setMdp($hasher->hashPassword($u, 'wecare123'));
        $u->setRole(RoleUtilisateur::Intervenant);
        $em->persist($u);

        $iv = new Intervenant();
        $iv->setUtilisateur($u);
        $iv->setAdminCreateur($this->getCurrentAdmin());
        $iv->setSpecialite($specialite);
        $iv->setTelephone($telephone ?: '0600000000');
        $iv->setDisponibilite(true);
        $iv->setRayonIntervention($rayon);
        $iv->setCreatedAt(new \DateTimeImmutable());
        $em->persist($iv);

        $em->flush();

        return $this->redirectToRoute('admin_soignants', ['id' => $iv->getId()]);
    }

    #[Route('/soignants/{id}/modifier', name: 'admin_soignants_modifier', methods: ['POST'])]
    public function modifier(
        int                   $id,
        Request               $request,
        IntervenantRepository $intervenantRepo,
        EntityManagerInterface $em,
    ): Response {
        $iv = $intervenantRepo->find($id);
        if (!$iv) return $this->redirectToRoute('admin_soignants');

        $prenom     = trim($request->request->get('prenom', ''));
        $nomFam     = trim($request->request->get('nom_famille', ''));
        $telephone  = trim($request->request->get('telephone', ''));
        $email      = trim($request->request->get('email', ''));
        $specialite = trim($request->request->get('specialite', 'Infirmier(e)'));
        $rayon = (float) $request->request->get('rayon', $iv->getRayonIntervention());

        $u = $iv->getUtilisateur();
        $nomComplet = trim($prenom . ' ' . $nomFam);
        if ($nomComplet !== ' ') $u->setNom($nomComplet);
        if ($email) $u->setEmail($email);

        $statut = $request->request->get('statut', 'actif');
        $iv->setSpecialite($specialite);
        $iv->setTelephone($telephone ?: $iv->getTelephone());
        $iv->setRayonIntervention($rayon);
        $iv->setStatut($statut);
        $em->flush();

        return $this->redirectToRoute('admin_soignants', ['id' => $id]);
    }

    #[Route('/soignants/{id}/supprimer', name: 'admin_soignants_supprimer', methods: ['POST'])]
    public function supprimer(
        int                    $id,
        Request                $request,
        IntervenantRepository  $intervenantRepo,
        EntityManagerInterface $em,
    ): Response {
        $iv = $intervenantRepo->find($id);
        if ($iv) {
            // Détacher du planning (côté propriétaire ManyToMany)
            foreach ($iv->getPlannings() as $planning) {
                $planning->removeIntervenant($iv);
            }
            // Supprimer l'utilisateur → cascade vers Intervenant → cascade vers Interventions
            $em->remove($iv->getUtilisateur());
            $em->flush();
        }

        return $this->redirectToRoute('admin_soignants');
    }
}
