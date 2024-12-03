<?php

namespace App\Repository;

use App\Entity\Reading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reading>
 *
 * @method Reading|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reading|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reading[]    findAll()
 * @method Reading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reading::class);
    }


    public function findLastYearReadingsByMonth(int $stationId): array
    {
        $dateFrom = new \DateTime();
        $dateFrom->modify('-365 days');

        $qb = $this->createQueryBuilder('r')
            ->select('MONTH(r.date) AS date, 
                      AVG(r.altitude) AS avgAltitude, 
                      AVG(r.temperature) AS avgTemperature, 
                      AVG(r.pressure) AS avgPressure, 
                      AVG(r.humidity) AS avgHumidity')
            ->where('r.station = :stationId')
            ->andWhere('r.date >= :dateFrom')
            ->groupBy('date')
            ->orderBy('r.date', 'DESC')
            ->setParameter('stationId', $stationId)
            ->setParameter('dateFrom', $dateFrom);

        return $qb->getQuery()->getResult();
    }

    public function findRecentReadingsByDay(int $stationId, int $days): array
    {
        $dateFrom = new \DateTime();
        $dateFrom->modify('-' . $days . ' days');

        $entityManager = $this->getEntityManager();

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
        SELECT DATE_FORMAT(r.date, \'%Y-%m-%d\') AS date, 
               AVG(r.altitude) AS avgAltitude, 
               AVG(r.temperature) AS avgTemperature, 
               AVG(r.pressure) AS avgPressure, 
               AVG(r.humidity) AS avgHumidity 
        FROM App\Entity\Reading r 
        WHERE r.station = :stationId 
        AND r.date >= :dateFrom 
        GROUP BY date 
        ORDER BY date DESC
    ')
            ->setParameter('stationId', $stationId)
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d'));

        return $query->getResult();
    }

    public function findRecentReadingsByHour(int $stationId, int $days): array
    {
        $dateFrom = new \DateTime();
        $dateFrom->modify('-' . $days . ' days');

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
        SELECT DATE_FORMAT(r.date, \'%Y-%m-%d %H:00:00\') AS date,
               AVG(r.altitude) AS avgAltitude,
               AVG(r.temperature) AS avgTemperature,
               AVG(r.pressure) AS avgPressure,
               AVG(r.humidity) AS avgHumidity
        FROM App\Entity\Reading r
        WHERE r.station = :stationId
        AND r.date >= :dateFrom
        GROUP BY date
        ORDER BY date DESC
    ')
            ->setParameter('stationId', $stationId)
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

    //    /**
    //     * @return Reading[] Returns an array of Reading objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reading
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
