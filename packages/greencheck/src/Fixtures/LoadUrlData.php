<?php

namespace TGWF\Greencheck\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use TGWF\Greencheck\Entity\GreencheckUrl;

class LoadUrlData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // should show as green
        $url = new GreencheckUrl();
        $url->setUrl('www.geluidsnet.nl');
        $url->setDatumBegin(new \DateTime('2009-01-01'));
        $url->setDatumEind(new \DateTime('2050-01-01'));
        $manager->persist($url);

        // Was green once, not anymore
        $url = new GreencheckUrl();
        $url->setUrl('was.greenbutexpired.nl');
        $url->setDatumBegin(new \DateTime('2008-01-01'));
        $url->setDatumEind(new \DateTime('2009-01-01'));
        $manager->persist($url);

        // Was green once, not anymore
        $url = new GreencheckUrl();
        $url->setUrl('www.bliin.com');
        $url->setDatumBegin(new \DateTime('2008-01-01'));
        $url->setDatumEind(new \DateTime('2009-01-01'));
        $manager->persist($url);

        // should show as green
        $url = new GreencheckUrl();
        $url->setUrl('no-www-registered.nl');
        $url->setDatumBegin(new \DateTime('2011-01-01'));
        $url->setDatumEind(new \DateTime('2050-01-01'));
        $manager->persist($url);

        // should show as green
        $url = new GreencheckUrl();
        $url->setUrl('www.marcgijzen.nl');
        $url->setDatumBegin(new \DateTime('2011-01-01'));
        $url->setDatumEind(new \DateTime('2050-01-01'));
        $manager->persist($url);

        // should show as green
        $url = new GreencheckUrl();
        $url->setUrl('www.arendjantetteroo.nl');
        $url->setDatumBegin(new \DateTime('2011-01-01'));
        $url->setDatumEind(new \DateTime('2050-01-01'));
        $manager->persist($url);

        $manager->flush();
    }
}
