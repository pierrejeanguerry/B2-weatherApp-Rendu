<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        
        // Testing station listing without authentication
        $client->request('GET', '/api/user/get');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
        $client->loginUser($testUser);
        $client->request(
                'GET', 
                '/api/user/get', 
                [],
                [], 
                ['token_user' => 'valid_token_here'],
            );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"username":"pj","email":"pj@pj.fr"}', $client->getResponse()->getContent());
    }

    public function testupdateUsername()
    {
        $client = static::createClient();

        $client->request('POST', '/api/user/username/update');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
        $client->loginUser($testUser);
        $client->jsonRequest(
            'POST', 
            '/api/user/username/update', 
            ["username" => "pj@pj.fr"], 
            ['token_user' => 'valid_token_here'],
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testupdateId()
    {
        $client = static::createClient();
        //no ids
        $client->request('POST', '/api/user/id/update');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        //wrong email
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
        $client->loginUser($testUser);
        $client->jsonRequest(
                'POST', 
                '/api/user/id/update', 
                [
                    "password" => "123456789Pj",
                    "email" => "newEmail"
                ], 
                ['token_user' => 'valid_token_here'],
            );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        //wrong password
        $client->jsonRequest(
            'POST', 
            '/api/user/id/update', 
            [
                "password" => "test",
                "email" => "pj@pj.com"
            ],
            ['token_user' => 'valid_token_here'],
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        //is ok
        $client->jsonRequest(
            'POST', 
            '/api/user/id/update', 
            [
                "password" => "123456789Pj",
                "email" => "pj@pj.com"
            ],
            ['token_user' => 'valid_token_here'],
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testDeleteId()
    {
        $client = static::createClient();
        //no ids
        $client->request('POST', '/api/user/id/delete');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        //wrong password
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
        $client->loginUser($testUser);
        $client->jsonRequest(
                'POST', 
                '/api/user/id/delete', 
                [
                    "password" => "123456789Pj",
                ], 
                ['token_user' => 'valid_token_here'],
            );
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        //is ok
        $client->jsonRequest(
            'POST', 
            '/api/user/id/delete', 
            [
                "password" => "pj",
            ],
            ['token_user' => 'valid_token_here'],
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
