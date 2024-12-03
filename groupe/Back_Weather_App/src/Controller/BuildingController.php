<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\User;
use App\Repository\BuildingRepository;
use App\Service\AuthManager;
use App\Service\RequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;




class BuildingController extends AbstractController
{
    #[Route('/api/buildings', name: 'building_list', methods: ['GET'], priority: 2)]
    public function index(
        #[CurrentUser()] User $user,
        Request $request,
        AuthManager $auth
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $buildings = $user->getBuildings();
            return $this->json(['buildings' => $buildings,], 200, [], [
                "groups" => ["building"]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/buildings', name: 'building_create', methods: ["POST"], priority: 2)]
    public function create(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        AuthManager $auth,
        RequestValidator $validator
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'building_name' => 'stringNotEmpty'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse)
                return $body;


            $manager->getConnection()->beginTransaction();

            $building = new Building;
            $building
                ->setName($body['building_name'])
                ->setUser($user);
            $manager->persist($building);
            $manager->flush();
            $manager->getConnection()->commit();
            return new JsonResponse(['message' => 'Building created'], 201);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('api/buildings/{id}', name: 'building_update', methods: ["PATCH"], priority: 2)]
    public function update_building(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        AuthManager $auth,
        BuildingRepository $repo,
        int $id,
        RequestValidator $validator
    ) {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;

            $requiredFields = [
                'new_name' => 'stringNotEmpty'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse)
                return $body;


            $building = $repo->findOneByUserId($user->getId(), $id);
            if ($building === null)
                return new JsonResponse(['error' => "Building not found"], 404);

            $manager->getConnection()->beginTransaction();

            $building->setName($body['new_name']);
            $manager->persist($building);
            $manager->flush();
            $manager->getConnection()->commit();
            return new JsonResponse(['message' => 'Building updated'], 200);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/api/buildings/{id}', name: 'building_delete', methods: ["DELETE"], priority: 2)]
    public function delete(
        #[CurrentUser()] User $user,
        Request $request,
        EntityManagerInterface $manager,
        BuildingRepository $repo,
        int $id,
        AuthManager $auth
    ): Response {
        try {
            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;
            $building = $repo->findOneByUserId($user->getId(), $id);
            if ($building === null)
                return new JsonResponse(['error' => "Building not found"], 404);

            if (!$building->getStations()->isEmpty())
                return new JsonResponse(['error' => 'Building is not empty'], 403);

            $manager->getConnection()->beginTransaction();

            $manager->remove($building);
            $manager->flush();
            $manager->getConnection()->commit();
            return new JsonResponse(['message' => 'Building deleted'], 200);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
