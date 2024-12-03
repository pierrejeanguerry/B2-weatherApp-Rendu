<?php

namespace App\Tests\Controller;

use App\Repository\BuildingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BuildingControllerTest extends WebTestCase
{
     public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/api/building/list');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
        $client->loginUser($testUser);
        $client->request(
                'GET', 
                '/api/building/list', 
                [],
                [], 
                ['token_user' => 'valid_token_here'],
            );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/api/building/create');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
        $client->loginUser($testUser);
        $client->jsonRequest(
                'POST', 
                '/api/building/create', 
                ['name_building' => 'test'],
                ['token_user' => 'valid_token_here'],
            );
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }
}
