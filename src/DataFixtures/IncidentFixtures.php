<?php

namespace App\DataFixtures;

use App\Entity\Incident;
use App\Entity\Intervention;
use App\Enum\GraviteIncident;
use App\Enum\StatutIncident;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IncidentFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [InterventionFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $incidentsData = [
            [
                'intervention'   => 'intervention_0',
                'description'    => 'Simone a chuté légèrement en se levant du fauteuil. Aucune blessure constatée. La famille a été informée et le médecin traitant contacté par précaution.',
                'gravite'        => GraviteIncident::Moderee,
                'statut'         => StatutIncident::Resolu,
                'zone'           => 'Salon',
                'dateSignalement'=> '-8 days 09:30',
            ],
            [
                'intervention'   => 'intervention_1',
                'description'    => 'Denise a signalé une douleur thoracique passagère en cours de séance. Arrêt immédiat de l\'activité. Médecin appelé, fausse alerte confirmée.',
                'gravite'        => GraviteIncident::Grave,
                'statut'         => StatutIncident::EnTraitement,
                'zone'           => 'Chambre',
                'dateSignalement'=> '-7 days 10:00',
            ],
        ];

        foreach ($incidentsData as $data) {
            $inc = new Incident();
            $inc->setIntervention($this->getReference($data['intervention'], Intervention::class));
            $inc->setDescription($data['description']);
            $inc->setGravite($data['gravite']);
            $inc->setStatut($data['statut']);
            $inc->setZone($data['zone']);
            $inc->setDateSignalement(new \DateTime($data['dateSignalement']));
            $manager->persist($inc);
        }

        $manager->flush();
    }
}
