<?php

namespace App\Controller;

use App\Entity\Reading;
use App\Entity\User;
use App\Repository\ReadingRepository;
use App\Repository\StationRepository;
use App\Service\AuthManager;
use App\Service\RequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ReadingController extends AbstractController
{
    #[Route('/api/readings', name: 'days_list_reading', methods: ["GET"], priority: 2)]
    public function list(
        #[CurrentUser()] User $user,
        Request $request,
        StationRepository $stationRepo,
        AuthManager $auth,
        ReadingRepository $repo,
        RequestValidator $validator
    ): Response {
        try {

            if (($authResponse = $auth->checkAuth($user, $request)) !== null)
                return $authResponse;
            $requiredFields = [
                'station_id' => 'numeric',
                'days' => 'numeric'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse)
                return $body;

            $station = $stationRepo->findOneByUserId($user->getId(), $body["station_id"]);
            if ($station === null)
                return new JsonResponse(['error' => 'Invalid field values'], 400);
            $readings = [];
            if ($body["days"] == 365)
                $readings = $repo->findLastYearReadingsByMonth($station->getId());
            if ($body["days"] == 30 || $body["days"] == 7)
                $readings = $repo->findRecentReadingsByDay($station->getId(), $body["days"]);
            if ($body["days"] == 1)
                $readings = $repo->findRecentReadingsByHour($station->getId(), $body["days"]);
            return $this->json(['readings' => $readings], 200, [], [
                'groups' => ['reading']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/readings', name: 'send_reading', methods: ["POST"], priority: 2)]
    public function send(Request $request, EntityManagerInterface $manager, StationRepository $stationRepo, RequestValidator $validator): Response
    {
        try {
            $requiredFields = [
                'mac_address' => 'stringNotEmpty',
                'temperature' => 'numeric',
                'altitude' => 'numeric',
                'pressure' => 'numeric',
                'humidity' => 'numeric'
            ];
            $body = $validator->validateJsonRequest($request, $requiredFields);
            if ($body instanceof JsonResponse)
                return $body;

            $station = $stationRepo->findOneBy(["mac" => $body['mac_address']]);
            if ($station === null)
                return new JsonResponse(['error' => 'Invalid MAC address'], 400);

            $manager->getConnection()->beginTransaction();

            $reading = new Reading();
            $station->setState(1);

            //verification des valeurs trop faibles ou trop élevées
            if ($body["temperature"] <= -50 || $body["temperature"] >= 60)
                $body["temperature"] = null;
            if ($body['altitude'] <= -1000 || $body['altitude'] >= 5000)
                $body['altitude'] = null;
            if ($body['pressure'] < 900 || $body['pressure'] > 1100)
                $body['pressure'] = null;
            if ($body['humidity'] < 0 || $body['humidity'] > 100)
                $body['humidity'] = null;
            if ($body['temperature'] == null || $body['altitude'] == null || $body['pressure'] == null || $body['humidity'] == null)
                $station->setState(2);

            $reading
                ->setTemperature($body['temperature'])
                ->setAltitude($body['altitude'])
                ->setPressure($body['pressure'])
                ->setHumidity($body['humidity'])
                ->setDate(new \DateTime())
                ->setStation($station);
            $manager->persist($reading);
            $manager->persist($station);
            $manager->flush();

            $manager->getConnection()->commit();

            return new JsonResponse(['message' => 'Created'], 201);
        } catch (\Exception $e) {
            $manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
