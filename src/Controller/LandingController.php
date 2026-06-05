<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
}
