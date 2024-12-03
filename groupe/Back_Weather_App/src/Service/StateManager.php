<?php

namespace App\Service;

use App\Repository\ReadingRepository;
use DateTimeZone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\JsonResponse;

class StateManager
{
    private $manager;
    private $readingRepo;

    public function __construct(EntityManagerInterface $manager, ReadingRepository $readingRepo)
    {
        $this->manager = $manager;
        $this->readingRepo = $readingRepo;
    }

    public function refreshStationsState($stations): JsonResponse|null
    {
        try {
            $this->manager->getConnection()->beginTransaction();

            foreach ($stations as $station) {

                $id = $station->getId();
                $readings = $this->readingRepo->findBy(["id" => $id]);
                if (!empty($readings)) {
                    $latestReading = end($readings);
                    $readingTime = $latestReading->getDate();
                    $currentTime = new \DateTime('now', new DateTimeZone('Europe/Paris'));
                    $currentTime->sub(new \DateInterval('PT1H'));
                    $readingTime->setTimeZone(new DateTimeZone('Europe/Paris'));
                    if ($readingTime < $currentTime) {
                        $station->setState(0);
                        $this->manager->persist($station);
                        $this->manager->flush();
                    }
                }
            }
            $this->manager->getConnection()->commit();
            return null;
        } catch (\Exception $e) {
            $this->manager->getConnection()->rollBack();
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
