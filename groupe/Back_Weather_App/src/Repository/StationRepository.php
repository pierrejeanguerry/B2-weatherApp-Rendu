<?php

namespace App\Repository;

use App\Entity\Station;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Station>
 *
 * @method Station|null find($id, $lockMode = null, $lockVersion = null)
 * @method Station|null findOneBy(array $criteria, array $orderBy = null)
 * @method Station[]    findAll()
 * @method Station[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Station::class);
    }

    public function findAllStationsByUserId(int $userId): array
    {
        return $this->createQueryBuilder('station')
            ->innerJoin('station.building', 'building')
            ->innerJoin('building.user', 'user')
            ->where('user.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserId(int $userId, int $buildingId)
    {
        return $this->createQueryBuilder('station')
            ->innerJoin('station.building', 'building')
            ->innerJoin('building.user', 'user')
            ->where('building.id = :buildingId')
            ->andWhere('user.id = :userId')
            ->setParameter('buildingId', $buildingId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserId(int $userId, int $stationId)
    {
        return $this->createQueryBuilder('station')
            ->innerJoin('station.building', 'building')
            ->innerJoin('building.user', 'user')
            ->where('station.id = :stationId')
            ->andWhere('user.id = :userId')
            ->setParameter('stationId', $stationId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Station[] Returns an array of Station objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Station
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
