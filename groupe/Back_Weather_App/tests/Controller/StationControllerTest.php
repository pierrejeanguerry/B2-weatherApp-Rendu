<?php


namespace App\Tests\Controller;

use App\Repository\BuildingRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StationControllerTest extends WebTestCase 
{
    public function testIndex()
    {
        $client = static::createClient();
        
        // Testing station listing without authentication
        $client->request('POST', '/api/station/list');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    //     $userRepository = static::getContainer()->get(UserRepository::class);
    //     $testUser = $userRepository->findOneBy(['email' => 'pj@pj.fr']);
    //     $buildingRepository = static::getContainer()->get(BuildingRepository::class);
    //     $roomRepository = static::getContainer()->get(RoomRepository::class);
    //     $testBuildingList = $buildingRepository->findAll(["user" => $testUser]);
    //     $testBuilding = $testBuildingList[0];
    //     $testRoomList = $roomRepository->findAll(["building" => $testBuilding]);
    //     $testRoom = $testRoomList[0];
    //     $client->loginUser($testUser, 'main' , ["token_user" => 'valid_token_here']);
    //     $client->request(
    //         'POST', 
    //         '/api/station/list', 
    //         ['room_id' => $testRoom->getId()],
    //         [], 
    //         ['token_user' => 'valid_token_here'],
    //     );
        
    //     $this->assertEquals(200, $client->getResponse()->getStatusCode());
    //     // Add more assertions to validate the response body

    //     // You can add more tests for different scenarios
    // }

    // public function testCreate()
    // {
    //     $client = static::createClient();

    //     // Testing station creation without authentication
    //     $client->jsonRequest('POST', '/api/station/create');
    //     $this->assertEquals(401, $client->getResponse()->getStatusCode());

    //     // Testing station creation with valid authentication
    //     // You may need to adjust this based on your authentication mechanism
    //     $client->setServerParameter('token_user', 'valid_token_here');
    //     // Add more tests to cover different scenarios of station creation
    // }

    // public function testDelete()
    // {
    //     $client = static::createClient();

    //     // Testing station deletion without authentication
    //     $client->jsonRequest('POST', '/api/station/delete');
    //     $this->assertEquals(401, $client->getResponse()->getStatusCode());

    //     // Testing station deletion with valid authentication
    //     // You may need to adjust this based on your authentication mechanism
    //     $client->setServerParameter('token_user', 'valid_token_here');
    //     // Add more tests to cover different scenarios of station deletion
    // }
}
