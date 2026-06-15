<?php

namespace App\Controller\Api;

use App\Entity\DemandeDevis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DevisController extends AbstractController
{
    #[Route('/api/devis', name: 'api_devis_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $required = ['prenom', 'nom', 'structure', 'typeStructure', 'email', 'telephone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new JsonResponse(['error' => 'Champ manquant : ' . $field], 400);
            }
        }

        $devis = new DemandeDevis();
        $devis->setPrenom($data['prenom']);
        $devis->setNom($data['nom']);
        $devis->setEmail($data['email']);
        $devis->setTelephone($data['telephone']);
        $devis->setStructure($data['structure']);
        $devis->setTypeStructure($data['typeStructure']);
        $devis->setNbIntervenants($data['nbIntervenants'] ?? null);
        $devis->setNbBeneficiaires($data['nbBeneficiaires'] ?? null);
        $devis->setPlan($data['plan'] ?? 'gratuit');
        $devis->setMessage($data['message'] ?? null);

        $em->persist($devis);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $devis->getId()], 201);
    }
}
