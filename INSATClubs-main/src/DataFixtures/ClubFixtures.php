<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Club;



class ClubFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for($i=1;$i<=10;$i++)
        {
            $club = new Club();
            $club->setNom("nom du club n°$i");
            $manager->persist($club);
        }

        $manager->flush();
    }
}
