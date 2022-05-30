<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterTestControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Register');
    }
}
