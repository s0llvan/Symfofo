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

        $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'profile[signature]' => 'Lorem ipsum',
        ]);
    }
}
