<?php

namespace App\Controller\Beneficiaire;

use App\Repository\InterventionRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/beneficiaire', name: 'beneficiaire_')]
class PageController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        InterventionRepository $interventionRepo,
        NotificationRepository $notificationRepo,
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        $now = new \DateTime();
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $prochaine = $beneficiaire
            ? $interventionRepo->findNextByBeneficiaire($beneficiaire, $now)
            : null;

        $interventionsAujourdhui = $beneficiaire
            ? $interventionRepo->findByBeneficiaireAndDateRange($beneficiaire, $today, $tomorrow)
            : [];

        $debutSemaine = new \DateTime('monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days')->setTime(23, 59, 59);
        $semaine = $beneficiaire
            ? $interventionRepo->findByBeneficiaireAndDateRange($beneficiaire, $debutSemaine, $finSemaine)
            : [];

        $compteRendus = $beneficiaire
            ? $interventionRepo->findWithCompteRenduByBeneficiaire($beneficiaire)
            : [];

        $nonLues = $notificationRepo->countUnreadByUser($user);

        // Grouper semaine par jour
        $joursSemaine = [];
        $jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $cursor = clone $debutSemaine;
        for ($i = 0; $i < 7; $i++) {
            $joursSemaine[] = ['label' => $jours[$i], 'date' => $cursor->format('Y-m-d'), 'interventions' => []];
            $cursor->modify('+1 day');
        }
        foreach ($semaine as $intervention) {
            $key = $intervention->getDateDebut()->format('Y-m-d');
            foreach ($joursSemaine as &$j) {
                if ($j['date'] === $key) {
                    $j['interventions'][] = $intervention;
                }
            }
        }

        return $this->render('beneficiaire/dashboard.html.twig', [
            'nom' => $user->getNom(),
            'prochaine' => $prochaine,
            'interventionsAujourdhui' => $interventionsAujourdhui,
            'joursSemaine' => $joursSemaine,
            'compteRendus' => $compteRendus,
            'nonLues' => $nonLues,
        ]);
    }

    #[Route('/planning', name: 'planning')]
    public function planning(): Response
    {
        return $this->render('beneficiaire/planning.html.twig');
    }

    #[Route('/comptes-rendus', name: 'comptes_rendus')]
    public function comptesRendus(InterventionRepository $interventionRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();
        $interventions = $beneficiaire
            ? $interventionRepo->findWithCompteRenduByBeneficiaire($beneficiaire)
            : [];
        return $this->render('beneficiaire/comptes_rendus.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route('/soignants', name: 'soignants')]
    public function soignants(InterventionRepository $interventionRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        $intervenants = $beneficiaire
            ? $interventionRepo->findDistinctIntervenantsByBeneficiaire($beneficiaire)
            : [];

        $debutSemaine = new \DateTime('monday this week');
        $finSemaine = (clone $debutSemaine)->modify('+6 days')->setTime(23, 59, 59);

        $soignantsData = [];
        foreach ($intervenants as $intervenant) {
            $interventionsSemaine = $interventionRepo->findByIntervenantBeneficiaireAndDateRange(
                $intervenant, $beneficiaire, $debutSemaine, $finSemaine
            );
            $jours = [];
            $cursor = clone $debutSemaine;
            $joursLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
            for ($i = 0; $i < 7; $i++) {
                $jours[] = [
                    'label' => $joursLabels[$i],
                    'num' => $cursor->format('j'),
                    'date' => $cursor->format('Y-m-d'),
                    'heures' => [],
                ];
                $cursor->modify('+1 day');
            }
            foreach ($interventionsSemaine as $inv) {
                $key = $inv->getDateDebut()->format('Y-m-d');
                foreach ($jours as &$j) {
                    if ($j['date'] === $key) {
                        $j['heures'][] = $inv->getDateDebut()->format('H\h');
                    }
                }
            }
            $soignantsData[] = ['intervenant' => $intervenant, 'jours' => $jours];
        }

        return $this->render('beneficiaire/soignants.html.twig', [
            'soignantsData' => $soignantsData,
        ]);
    }

    #[Route('/messages', name: 'messages')]
    public function messages(\App\Repository\MessageRepository $messageRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();
        $messages = $beneficiaire
            ? $messageRepo->findConversationsByBeneficiaire($user, $beneficiaire)
            : [];
        $nonLusMsg = $messageRepo->countUnreadByUser($user);
        return $this->render('beneficiaire/messages.html.twig', [
            'messages' => $messages,
            'nonLusMsg' => $nonLusMsg,
        ]);
    }

    #[Route('/notifications', name: 'notifications')]
    public function notifications(NotificationRepository $notificationRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        return $this->render('beneficiaire/notifications.html.twig', [
            'notifications' => $notificationRepo->findByUser($user),
            'nonLues' => $notificationRepo->countUnreadByUser($user),
        ]);
    }

    #[Route('/mon-compte', name: 'mon_compte')]
    public function monCompte(): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();
        return $this->render('beneficiaire/mon_compte.html.twig', [
            'user' => $user,
            'beneficiaire' => $beneficiaire,
        ]);
    }

    #[Route('/contact-wecare', name: 'contact_wecare')]
    public function contactWecare(): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();
        return $this->render('beneficiaire/contact_wecare.html.twig', [
            'beneficiaire' => $beneficiaire,
        ]);
    }

    #[Route('/aidant/ajouter', name: 'aidant_ajouter', methods: ['POST'])]
    public function aidantAjouter(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();
        if ($beneficiaire) {
            $nom    = trim($request->request->get('nom', ''));
            $prenom = trim($request->request->get('prenom', ''));
            if ($nom) {
                $aidant = new \App\Entity\Aidant();
                $aidant->setNom($nom);
                $aidant->setPrenom($prenom ?: null);
                $aidant->setTelephone($request->request->get('telephone') ?: null);
                $aidant->setEmail($request->request->get('email') ?: null);
                $aidant->setLien($request->request->get('lien') ?: null);
                $aidant->setBeneficiaire($beneficiaire);
                $em->persist($aidant);
                $em->flush();
                $this->addFlash('success', 'Aidant ajouté avec succès.');
            }
        }
        return $this->redirectToRoute('beneficiaire_mon_compte');
    }

    #[Route('/aidant/{id}/retirer', name: 'aidant_retirer', methods: ['POST'])]
    public function aidantRetirer(int $id, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();
        $aidant = $em->getRepository(\App\Entity\Aidant::class)->find($id);
        if ($aidant && $aidant->getBeneficiaire() === $beneficiaire) {
            $em->remove($aidant);
            $em->flush();
            $this->addFlash('success', 'Aidant retiré avec succès.');
        }
        return $this->redirectToRoute('beneficiaire_mon_compte');
    }

    #[Route('/contact-wecare/envoyer', name: 'contact_wecare_envoyer', methods: ['POST'])]
    public function contactWecareEnvoyer(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        $motif = $request->request->get('motif', '');
        $texte = $request->request->get('texte', '');

        if ($motif && $texte) {
            $admin = $beneficiaire?->getAdminCreateur();
            if ($admin) {
                $message = new \App\Entity\Message();
                $message->setExpediteur($user);
                $message->setDestinataire($admin->getUtilisateur());
                $message->setBeneficiaire($beneficiaire);
                $message->setContenu('[' . $motif . '] ' . $texte);
                $message->setDateEnvoi(new \DateTime());
                $message->setLu(false);
                $message->setType(\App\Enum\TypeMessage::Direct);
                $em->persist($message);
                $em->flush();
            }
            $this->addFlash('success', 'Votre message a bien été envoyé à WeCare.');
        } else {
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
        }

        return $this->redirectToRoute('beneficiaire_contact_wecare');
    }
}
