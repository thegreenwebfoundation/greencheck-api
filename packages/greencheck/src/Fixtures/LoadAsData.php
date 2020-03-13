<?php

namespace TGWF\Greencheck\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

use TGWF\Greencheck\Entity\GreencheckAs;

class LoadAsData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $hostingprovider = new \TGWF\Greencheck\Entity\Hostingprovider();
        $hostingprovider->setCountrydomain('NL');
        $hostingprovider->setNaam('AS Hoster');
        $hostingprovider->setPartner('True');
        $hostingprovider->setWebsite('http://www.ashoster.nl');
        $hostingprovider->setModel($hostingprovider::MODEL_GREENENERGY);
        $hostingprovider->setShowonwebsite(true);

        $manager->persist($hostingprovider);

        $as = new GreencheckAs();
        $as->setAsn(49750);
        $as->setActive(true);
        $as->setHostingprovider($hostingprovider);

        $manager->persist($as);

        $as = new GreencheckAs();
        $as->setAsn(50673);
        $as->setActive(true);
        $as->setHostingprovider($hostingprovider);

        $manager->persist($as);

        $manager->flush();
        /*
                        $db->query(<<<EOT
                INSERT INTO `greencheck_as` (`id`, `id_hp`, `asn`, `active`) VALUES
        (2, 1, 50673 , 1), # for tests
        (6, 14, 16237, 1),
        (45, 9, 47172, 1 ),
        (46, 59, 12859, 1 ),
        (48, 61, 1140, 1),
        (49, 63, 29659, 1),
        (50, 64, 39556, 1),
        (83, 101, 24586, 1),
        (85, 105, 20857, 1),
        (89, 114, 25542, 1),
        (90, 120, 34233, 1),
        (111, 168, 42158, 1),
        (94, 131, 24940, 1),
        (97, 134, 286, 1),
        (99, 134, 8737, 1),
        (101, 143, 12414, 1),
        (103, 154, 25525, 1),
        (104, 158, 35260, 1),
        (106, 33, 36363, 1),
        (107, 163, 48635, 1),
        (109, 166, 16243, 1),
        (110, 167, 51313, 1),
        (120, 186, 49750, 1);
        EOT
                        );*/
    }
}
