<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityHandlerTest extends WebTestCase
{
    public function testSomething(): void
    {

        $client = static::createClient();
        // Effectue une requête pour obtenir une session
        $client->request('GET', '/');
        // Attendre 30 secondes sans effectuer d'action
        sleep(30);

        $client->request('GET', '/');
        $this->assertResponseRedirects('/logout');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
