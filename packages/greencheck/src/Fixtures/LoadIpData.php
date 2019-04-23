<?php

namespace TGWF\Greencheck\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\Greencheck\Entity\GreencheckAs;

class LoadIpData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $hostingprovider = new \TGWF\Greencheck\Entity\Hostingprovider();
        $hostingprovider->setCountrydomain('NL');
        $hostingprovider->setNaam('Groene Hosting');
        $hostingprovider->setWebsite('http://www.groenehosting.nl');
        $hostingprovider->setPartner('False');
        $hostingprovider->setModel($hostingprovider::MODEL_GREENENERGY);
        $hostingprovider->setShowonwebsite(true);

        $manager->persist($hostingprovider);

        $ip = new GreencheckIp();
        $ip->setIpStart('94.75.237.70');
        $ip->setIpEind('94.75.237.100');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);

        $manager->persist($ip);

        $hostingprovider = new \TGWF\Greencheck\Entity\Hostingprovider();
        $hostingprovider->setCountrydomain('NL');
        $hostingprovider->setNaam('Xs4all');
        $hostingprovider->setWebsite('http://www.xs4all.nl');
        $hostingprovider->setPartner('True');
        $hostingprovider->setModel($hostingprovider::MODEL_GREENENERGY);
        $hostingprovider->setShowonwebsite(true);

        $manager->persist($hostingprovider);

        $ip = new GreencheckIp();
        $ip->setIpStart('94.75.237.88');
        $ip->setIpEind('94.75.237.100');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);

        $manager->persist($ip);

        $ip = new GreencheckIp();
        $ip->setIpStart('194.109.21.4');
        $ip->setIpEind('194.109.21.4');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);

        $manager->persist($ip);

        $ip = new GreencheckIp();
        $ip->setIpStart('94.75.237.89');
        $ip->setIpEind('94.75.237.89');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);

        $manager->persist($ip);
        
        $ip = new GreencheckIp();
        $ip->setIpStart('217.26.124.106');
        $ip->setIpEind('217.26.124.106');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);
        
        $manager->persist($ip);
        
        /**
         * @todo this needs to move to LoadAsData, and get a reference to the xs4all hostingprovider
         * @var GreencheckAs
         */
        $as = new GreencheckAs();
        $as->setAsn(3265);
        $as->setActive(true);
        $as->setHostingprovider($hostingprovider);

        $manager->persist($as);

        /**
         * Store ipv6 data
         * @var [type]
         */
        $hostingprovider = new \TGWF\Greencheck\Entity\Hostingprovider();
        $hostingprovider->setCountrydomain('NL');
        $hostingprovider->setNaam('Netcompany');
        $hostingprovider->setWebsite('http://www.netcompany.nl');
        $hostingprovider->setPartner('True');
        $hostingprovider->setModel($hostingprovider::MODEL_GREENENERGY);
        $hostingprovider->setShowonwebsite(true);

        $manager->persist($hostingprovider);

        $ip = new GreencheckIp();
        $ip->setIpStart('2001:4b98:dc0:41:216:3eff:fedd:3317');
        $ip->setIpEind('2001:4b98:dc0:41:216:3eff:fedd:3317');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);

        $manager->persist($ip);

        // 2a00:1950:100::/48
        $ip = new GreencheckIp();
        $ip->setIpStart('2a00:1950:0100:0000:0000:0000:0000:0000');
        $ip->setIpEind('2a00:1950:0100:ffff:ffff:ffff:ffff:ffff');
        $ip->setActive(true);
        $ip->setHostingprovider($hostingprovider);

        $manager->persist($ip);

        $manager->flush();
    }
}
