<?php

namespace App\Controller;

use App\Entity\Station;
use App\Entity\User;
use App\Repository\StationRepository;
use App\Repository\BuildingRepository;
use App\Service\AuthManager;
use App\Service\RequestValidator;
use App\Service\StateManager;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class StationController extends AbstractController
{
    #[Route('/api/stations', name: 'station_list', methods: ['GET'], priority: 2)]
    public function list(
        #[CurrentUser()] User $user,
        Request $request,
        StationRepository $repo,
        AuthManager $auth,
        RequestValidator $validator,
        StateManager $stateManager
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'building_id' => 'numeric'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse) {
                return $body;
            }

            $stations = ($body['building_id'] == 0)
                ? $repo->findAllStationsByUserId($user->getId())
                : $repo->findByUserId($user->getId(), $body["building_id"]);

            $return = $stateManager->refreshStationsState($stations);
            if ($return instanceof JsonResponse) {
                return $return;
            }
            return $this->json(["stations" => $stations,], 200, [], [
                "groups" => ["station"]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/stations', name: 'station_create', methods: ["POST"], priority: 2)]
    public function create(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        StationRepository $repo,
        BuildingRepository $buildingRepo,
        AuthManager $auth,
        RequestValidator $validator
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'mac_address' => 'stringNotEmpty',
                'station_name' => 'stringNotEmpty',
                'building_id' => 'numeric'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse)
                return $body;

            $stationExist = $repo->findOneBy(['mac' => $body['mac_address']]);
            if ($stationExist)
                return new JsonResponse(['error' => "Station already used"], 409);

            $building = $buildingRepo->findOneByUserId($user->getId(), $body['building_id']);
            if ($building === null)
                return new JsonResponse(['error' => "Building not found"], 404);

            $manager->getConnection()->beginTransaction();

            $station = new Station();
            $station
                ->setMac($body['mac_address'])
                ->setBuilding($building)
                ->setActivationDate(new \DateTime('now', new DateTimeZone('Europe/Paris')))
                ->setName($body['station_name']);
            $manager->persist($station);
            $manager->flush();

            $manager->getConnection()->commit();
            return new JsonResponse(['message' => 'Station created'], 201);
        } catch (Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('api/stations/{id}', name: 'station_update', methods: ["PATCH"], priority: 2)]
    public function update(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        AuthManager $auth,
        StationRepository $repo,
        BuildingRepository $buildingRepo,
        int $id,
        RequestValidator $validator
    ) {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'new_name' => 'string',
                'new_building_id' => 'numeric'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse)
                return $body;

            $station = $repo->findOneByUserId($user->getId(), $id);
            if ($station === null)
                return new JsonResponse(['error' => "Station not found"], 404);

            $manager->getConnection()->beginTransaction();

            if (!empty($body['new_name']))
                $station->setName($body['new_name']);

            if ($body['new_building_id'] !== 0) {
                $building = $buildingRepo->findOneByUserId($user->getId(), $body['building_id']);
                if ($building === null)
                    return new JsonResponse(['error' => "Building not found"], 404);

                $station->setBuilding($building);
            }
            $manager->persist($station);
            $manager->flush();

            $manager->getConnection()->commit();

            return new JsonResponse(['message' => 'Station updated'], 200);
        } catch (Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/stations/{id}', name: 'station_delete', methods: ["DELETE"], priority: 2)]
    public function delete(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        StationRepository $repo,
        AuthManager $auth,
        int $id
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $station = $repo->findOneByUserId($user->getId(), $id);
            if ($station === null)
                return new JsonResponse(['error' => "Station not found"], 404);

            $manager->getConnection()->beginTransaction();

            $manager->remove($station);
            $manager->flush();
            $manager->getConnection()->commit();
            return new JsonResponse(['message' => 'Station deleted'], 200);
        } catch (Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
