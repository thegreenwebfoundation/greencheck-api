<?php

namespace TGWF\Greencheck\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use TGWF\Greencheck\Entity\GreencheckTld;

class LoadTldData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $tld = new GreencheckTld();
        $tld->setTld('com');
        $tld->setToplevel('com');
        $tld->setCheckedDomains(4040562);
        $tld->setGreenDomains(308255);
        $tld->setHps(50);

        $manager->persist($tld);

        $tld = new GreencheckTld();
        $tld->setTld('nl');
        $tld->setToplevel('nl');
        $tld->setCheckedDomains(2488693);
        $tld->setGreenDomains(896606);
        $tld->setHps(117);

        $manager->persist($tld);

        $tld = new GreencheckTld();
        $tld->setTld('fr');
        $tld->setToplevel('fr');
        $tld->setCheckedDomains(61061);
        $tld->setGreenDomains(2543);
        $tld->setHps(0);

        $manager->persist($tld);
        
        $manager->flush();
    }
}
