<?php

namespace LouvreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use  Symfony\Component\Routing\Annotation\Route;

class DefaultControllerTest extends WebTestCase
{
/*     public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    } 
*/

   public function testTestBilletsAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/testbillets?date=2018/01/01');

        $this->assertContains('placesRestantes', $client->getResponse()->getContent());
    }

}
