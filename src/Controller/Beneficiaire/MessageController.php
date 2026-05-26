<?php

namespace App\Controller\Beneficiaire;

use App\Entity\Message;
use App\Enum\TypeMessage;
use App\Repository\MessageRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/beneficiaire', name: 'api_beneficiaire_')]
class MessageController extends AbstractController
{
    #[Route('/messages', name: 'messages_liste', methods: ['GET'])]
    public function liste(
        MessageRepository $messageRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $messages = $messageRepo->findConversationsByBeneficiaire($user, $beneficiaire);

        return $this->json([
            'nonLus' => $messageRepo->countUnreadByUser($user),
            'messages' => array_map(fn($m) => $this->serializeMessage($m, $user), $messages),
        ]);
    }

    #[Route('/messages', name: 'messages_envoyer', methods: ['POST'])]
    public function envoyer(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepo,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $beneficiaire = $user->getBeneficiaire();

        if (!$beneficiaire) {
            return $this->json(['error' => 'Bénéficiaire non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['contenu'])) {
            return $this->json(['error' => 'Le contenu est obligatoire'], 422);
        }

        if (empty($data['destinataireId'])) {
            return $this->json(['error' => 'Le destinataire est obligatoire'], 422);
        }

        $destinataire = $utilisateurRepo->find($data['destinataireId']);
        if (!$destinataire) {
            return $this->json(['error' => 'Destinataire non trouvé'], 404);
        }

        $message = new Message();
        $message->setExpediteur($user);
        $message->setDestinataire($destinataire);
        $message->setBeneficiaire($beneficiaire);
        $message->setContenu($data['contenu']);
        $message->setDateEnvoi(new \DateTime());
        $message->setLu(false);
        $message->setType(TypeMessage::Direct);

        $em->persist($message);
        $em->flush();

        return $this->json([
            'id' => $message->getId(),
            'message' => 'Message envoyé avec succès',
        ], 201);
    }

    #[Route('/messages/{id}/lire', name: 'message_lire', methods: ['PATCH'])]
    public function marquerLu(
        int $id,
        MessageRepository $messageRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        $message = $messageRepo->find($id);
        if (!$message || $message->getDestinataire() !== $user) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }

        $message->setLu(true);
        $em->flush();

        return $this->json(['message' => 'Message marqué comme lu']);
    }

    private function serializeMessage(\App\Entity\Message $m, \App\Entity\Utilisateur $user): array
    {
        $expediteur = $m->getExpediteur();
        $estMoi = $expediteur->getId() === $user->getId();

        return [
            'id' => $m->getId(),
            'expediteur' => [
                'id' => $expediteur->getId(),
                'nom' => $expediteur->getNom(),
            ],
            'contenu' => $m->getContenu(),
            'dateEnvoi' => $m->getDateEnvoi()?->format('d/m/Y H:i'),
            'lu' => $m->isLu(),
            'type' => $m->getType()?->value,
            'estMoi' => $estMoi,
        ];
    }
}
