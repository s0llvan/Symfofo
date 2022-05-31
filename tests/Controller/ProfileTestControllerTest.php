<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileTestControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByUsername('admin');

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();

        $crawler = $client->submitForm('Save', [
            'profile[email]' => 'admin@local.host',
        ]);
    }
}