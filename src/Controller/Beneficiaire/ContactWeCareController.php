<?php

namespace App\Controller\Beneficiaire;

use App\Entity\Message;
use App\Enum\TypeMessage;
use App\Repository\AdministrateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class ContactWeCareController extends AbstractController
{
    #[Route('/contact-wecare', name: 'contact_wecare', methods: ['POST'])]
    public function contacter(
        Request $request,
        EntityManagerInterface $em,
        AdministrateurRepository $adminRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['objet']) || empty($data['message'])) {
            return $this->json(['error' => 'L\'objet et le message sont obligatoires'], 422);
        }

        $admin = $beneficiaire->getAdminCreateur();
        if (!$admin) {
            return $this->json(['error' => 'Aucun administrateur associé'], 404);
        }

        $message = new Message();
        $message->setExpediteur($user);
        $message->setDestinataire($admin->getUtilisateur());
        $message->setBeneficiaire($beneficiaire);
        $message->setContenu('[' . $data['objet'] . '] ' . $data['message']);
        $message->setDateEnvoi(new \DateTime());
        $message->setLu(false);
        $message->setType(TypeMessage::Direct);

        $em->persist($message);
        $em->flush();

        return $this->json(['message' => 'Votre message a été envoyé à WeCare'], 201);
    }
}
