<?php

namespace TGWF\Greencheck\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use TGWF\Greencheck\Entity\Greencheck;
use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\Greencheck\Entity\GreencheckBy;

class LoadGreencheckData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $hostingprovider = new \TGWF\Greencheck\Entity\Hostingprovider();
        $hostingprovider->setCountrydomain('NL');
        $hostingprovider->setNaam('Greencheck dummy provider');
        $hostingprovider->setWebsite('http://www.greenweb.nl');
        $hostingprovider->setModel($hostingprovider::MODEL_GREENENERGY);
        $hostingprovider->setPartner('True');
        $hostingprovider->setShowonwebsite(true);

        $manager->persist($hostingprovider);

        $gc = new Greencheck();
        $gc->setIdGreencheck(2);
        $gc->setIdHp($hostingprovider->getId());
        $gc->setType('ip');
        $gc->setGreen(true);
        $gc->setUrl('www.xs4all.nl');
        $gc->setDatum(new \DateTime('now'));

        $gc->setIp(GreencheckIp::convertIpPresentationToDecimal('194.109.21.4'));

        $manager->persist($gc);

        $gc = new Greencheck();
        $gc->setIdGreencheck(2);
        $gc->setIdHp($hostingprovider->getId());
        $gc->setType('ip');
        $gc->setGreen(true);
        $gc->setUrl('www.xs4all.nl');
        $gc->setDatum(new \DateTime('now'));
        $gc->setIp(GreencheckIp::convertIpPresentationToDecimal('194.109.21.4'));

        $manager->persist($gc);

        $gc = new Greencheck();
        $gc->setIdGreencheck(2);
        $gc->setIdHp($hostingprovider->getId());
        $gc->setType('ip');
        $gc->setGreen(true);
        $gc->setUrl('www.xs4all.nl');
        $gc->setDatum(new \DateTime('now'));
        $gc->setIp(GreencheckIp::convertIpPresentationToDecimal('194.109.21.4'));

        $manager->persist($gc);

        $gc = new Greencheck();
        $gc->setIdGreencheck(2);
        $gc->setIdHp($hostingprovider->getId());
        $gc->setType('ip');
        $gc->setGreen(true);
        $gc->setUrl('www.xs4all.nl');
        $gc->setDatum(new \DateTime('now'));
        $gc->setIp(GreencheckIp::convertIpPresentationToDecimal('194.109.21.4'));

        $manager->persist($gc);

        $manager->flush();
    }
}
