<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class ParametresController extends AbstractController
{
    #[Route('/parametres', name: 'admin_parametres')]
    public function index(): Response
    {
        return $this->render('admin/parametres.html.twig');
    }
}
