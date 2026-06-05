<?php

namespace App\DataFixtures;

use App\Entity\CompteRendu;
use App\Entity\Intervenant;
use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Enum\TypeNotification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NotificationFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class, CompteRenduFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $notifsData = [
            [
                'intervRef'  => 'interv_0',
                'crRef'      => null,
                'type'       => TypeNotification::Rappel,
                'message'    => 'Visite planifiée demain matin chez Fatima Musson à 10h00. Pensez à apporter le matériel de repas.',
                'lu'         => false,
                'createdAt'  => '-1 day',
            ],
            [
                'intervRef'  => 'interv_1',
                'crRef'      => 'cr_0',
                'type'       => TypeNotification::CompteRenduValide,
                'message'    => 'Votre compte-rendu pour Simone Ruault du ' . (new \DateTime('-8 days'))->format('d/m') . ' a été validé par le coordinateur.',
                'lu'         => true,
                'createdAt'  => '-6 days',
            ],
        ];

        foreach ($notifsData as $data) {
            $iv   = $this->getReference($data['intervRef'], Intervenant::class);
            $user = $iv->getUtilisateur();

            $n = new Notification();
            $n->setUtilisateur($user);
            $n->setType($data['type']);
            $n->setMessage($data['message']);
            $n->setLu($data['lu']);
            $n->setCreatedAt(new \DateTimeImmutable($data['createdAt']));

            if ($data['crRef'] !== null) {
                $n->setCompteRendu($this->getReference($data['crRef'], CompteRendu::class));
            }

            $manager->persist($n);
        }

        $manager->flush();
    }
}
