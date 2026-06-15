<?php

namespace App\Controller\Admin;

use App\Entity\DemandeDevis;
use App\Repository\DemandeDevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/devis', name: 'admin_devis_')]
class DevisController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(DemandeDevisRepository $repo): Response
    {
        $devis = $repo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/devis.html.twig', [
            'devis' => $devis,
        ]);
    }

    #[Route('/{id}/ajuster', name: 'ajuster', methods: ['POST'])]
    public function ajuster(DemandeDevis $devis, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['montant'])) $devis->setMontant((float) $data['montant']);
        if (isset($data['note']))    $devis->setNote($data['note']);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/envoyer', name: 'envoyer', methods: ['POST'])]
    public function envoyer(DemandeDevis $devis, EntityManagerInterface $em): JsonResponse
    {
        $devis->setStatut('envoye');
        $em->flush();
        // TODO: envoyer email au prospect avec le devis en PDF

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/signer', name: 'signer', methods: ['POST'])]
    public function signer(DemandeDevis $devis, EntityManagerInterface $em): JsonResponse
    {
        $devis->setStatut('signe');
        $em->flush();
        // TODO: créer le compte client et envoyer accès

        return new JsonResponse(['success' => true]);
    }
}
