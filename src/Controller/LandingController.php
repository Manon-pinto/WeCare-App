<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class LandingController extends AbstractController
{
    #[Route('/', name: 'landing_home')]
    public function home(): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_BENEFICIAIRE')) return $this->redirectToRoute('beneficiaire_dashboard');
            if ($this->isGranted('ROLE_ADMINISTRATEUR')) return $this->redirectToRoute('admin_dashboard');
            return $this->redirectToRoute('intervenant_dashboard');
        }
        return $this->render('landing/index.html.twig');
    }

    #[Route('/fonctionnalites', name: 'landing_features')]
    public function features(): Response
    {
        return $this->render('landing/fonctionnalites.html.twig');
    }

    #[Route('/tarifs', name: 'landing_pricing')]
    public function pricing(): Response
    {
        return $this->render('landing/tarifs.html.twig');
    }

    #[Route('/ressources', name: 'landing_ressources')]
    public function ressources(): Response
    {
        return $this->render('landing/ressources.html.twig');
    }

    #[Route('/contact', name: 'landing_contact')]
    public function contact(): Response
    {
        return $this->render('landing/contact.html.twig');
    }

    #[Route('/blog', name: 'landing_blog')]
    public function blog(): Response
    {
        return $this->render('landing/blog.html.twig');
    }

    #[Route('/mentions-legales', name: 'landing_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('landing/mentions-legales.html.twig');
    }

    #[Route('/conditions-de-vente', name: 'landing_conditions_vente')]
    public function conditionsVente(): Response
    {
        return $this->render('landing/conditions-vente.html.twig');
    }

    #[Route('/politique-cookies', name: 'landing_politique_cookies')]
    public function politiqueCookies(): Response
    {
        return $this->render('landing/politique-cookies.html.twig');
    }

    #[Route('/devis-envoye', name: 'landing_devis_envoye')]
    public function devisEnvoye(): Response
    {
        return $this->render('landing/devis-envoye.html.twig');
    }

    #[Route('/api/devis', name: 'api_devis', methods: ['POST'])]
    public function apiDevis(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $prenom        = $data['prenom']          ?? '';
        $nom           = $data['nom']             ?? '';
        $structure     = $data['structure']       ?? '';
        $typeStructure = $data['typeStructure']   ?? '';
        $email         = $data['email']           ?? '';
        $telephone     = $data['telephone']       ?? '';
        $plan          = $data['plan']            ?? '';
        $nbInter       = $data['nbIntervenants']  ?? '';
        $nbBenef       = $data['nbBeneficiaires'] ?? '';
        $message       = $data['message']         ?? '';

        if (!$prenom || !$nom || !$email || !$structure) {
            return new JsonResponse(['error' => 'Champs obligatoires manquants.'], 400);
        }

        $corps = "Nouvelle demande d'inscription WeCare\n\n"
            . "Nom : $prenom $nom\n"
            . "Structure : $structure ($typeStructure)\n"
            . "Email : $email\n"
            . "Téléphone : $telephone\n"
            . "Plan souhaité : $plan\n"
            . "Nb intervenants : $nbInter\n"
            . "Nb bénéficiaires : $nbBenef\n"
            . "Message : $message\n";

        $emailMsg = (new Email())
            ->from('noreply@wecare.fr')
            ->to('contact@wecare.fr')
            ->replyTo($email)
            ->subject("Nouvelle demande – $prenom $nom ($structure)")
            ->text($corps);

        $mailer->send($emailMsg);

        return new JsonResponse(['success' => true]);
    }
}
