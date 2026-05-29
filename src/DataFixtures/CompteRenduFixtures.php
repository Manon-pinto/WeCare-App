<?php

namespace App\DataFixtures;

use App\Entity\CompteRendu;
use App\Entity\Intervention;
use App\Enum\ModeRedaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CompteRenduFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [InterventionFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // Un CR par intervention Terminée (intervention_0 à 3 = semaine dernière)
        $crsData = [
            [
                'intervention' => 'intervention_0',
                'contenu'      => 'Simone bien réveillée. Toilette effectuée sans difficulté. Médicaments pris. État général stable. RAS.',
                'mode'         => ModeRedaction::Manuel,
                'decalage'     => '-8 days 20:00',
                'valide'       => true,
            ],
            [
                'intervention' => 'intervention_1',
                'contenu'      => 'Ménage effectué dans les pièces principales. Denise de bonne humeur. A exprimé le souhait d\'avoir plus de yaourts lors de la prochaine livraison de courses.',
                'mode'         => ModeRedaction::Manuel,
                'decalage'     => '-7 days 20:00',
                'valide'       => true,
            ],
            [
                'intervention' => 'intervention_2',
                'contenu'      => 'Fatima fatiguée en début de séance. Repas préparé et servi. A mangé la moitié de l\'assiette. Légère tristesse mentionnée — à surveiller lors des prochaines visites.',
                'mode'         => ModeRedaction::AssistelA,
                'decalage'     => '-6 days 20:00',
                'valide'       => false,
            ],
            [
                'intervention' => 'intervention_3',
                'contenu'      => 'Accompagnement chez le médecin traitant effectué. Émile de bonne humeur. Ordonnance renouvelée. Retour au domicile sans incident.',
                'mode'         => ModeRedaction::Vocal,
                'decalage'     => '-5 days 19:00',
                'valide'       => true,
            ],
        ];

        foreach ($crsData as $k => $data) {
            $cr = new CompteRendu();
            $cr->setIntervention($this->getReference($data['intervention'], Intervention::class));
            $cr->setContenu($data['contenu']);
            $cr->setModeRedaction($data['mode']);
            $cr->setDateRedaction(new \DateTime($data['decalage']));
            $cr->setValide($data['valide']);
            $manager->persist($cr);

            $this->addReference('cr_' . $k, $cr);
        }

        $manager->flush();
    }
}
