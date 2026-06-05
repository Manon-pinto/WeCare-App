<?php

namespace App\DataFixtures;

use App\Entity\Intervenant;
use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Enum\TypeMessage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MessageFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // On récupère les utilisateurs via leurs intervenants (qui ont une relation utilisateur)
        $interv0 = $this->getReference('interv_0', Intervenant::class);
        $interv1 = $this->getReference('interv_1', Intervenant::class);

        $adminUser  = $interv0->getAdminCreateur()->getUtilisateur();
        $interv0User = $interv0->getUtilisateur();
        $interv1User = $interv1->getUtilisateur();

        $messagesData = [
            [
                'expediteur'  => $adminUser,
                'destinataire'=> $interv0User,
                'contenu'     => 'Bonjour Léo, un rappel pour la visite de Simone demain matin à 8h. N\'oubliez pas le matériel de toilette.',
                'dateEnvoi'   => '-1 day 17:30',
                'lu'          => true,
                'type'        => TypeMessage::Direct,
            ],
            [
                'expediteur'  => $interv1User,
                'destinataire'=> $adminUser,
                'contenu'     => 'Bonjour, je souhaite signaler que Denise Huard m\'a mentionné des douleurs aux jambes lors de ma dernière visite. Pourriez-vous organiser un suivi médical ?',
                'dateEnvoi'   => '-2 days 14:15',
                'lu'          => false,
                'type'        => TypeMessage::Direct,
            ],
        ];

        foreach ($messagesData as $data) {
            $m = new Message();
            $m->setExpediteur($data['expediteur']);
            $m->setDestinataire($data['destinataire']);
            $m->setContenu($data['contenu']);
            $m->setDateEnvoi(new \DateTime($data['dateEnvoi']));
            $m->setLu($data['lu']);
            $m->setType($data['type']);
            $manager->persist($m);
        }

        $manager->flush();
    }
}
