<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testHomepage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'The Green Web Foundation Api Service');
    }

    public function testGreencheck()
    {
        // @todo figure out a way to check here that the consumer is running
        sleep(5);

        $client = static::createClient();
        $crawler = $client->request('GET', '/greencheck/www.nu.nl');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('www.nu.nl', $response['url']);
        $this->assertEquals(false, $response['green']);
    }
}
