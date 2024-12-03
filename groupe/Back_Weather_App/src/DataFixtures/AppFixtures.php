<?php

// namespace App\DataFixtures;

// use App\Entity\Building;
// use App\Entity\Room;
// use App\Entity\Station;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// use App\Entity\User;
// use Doctrine\Bundle\FixturesBundle\Fixture;
// use Doctrine\Persistence\ObjectManager;
// use Faker;

// class AppFixtures extends Fixture
// {
//     private UserPasswordHasherInterface $hasher;
//     public function __construct(UserPasswordHasherInterface $hasher)
//     {
//         $this->hasher = $hasher;
//     }

//     public function load(ObjectManager $manager)
//     {
//         $faker = Faker\Factory::create('fr_FR');
//         $t = microtime(true);

//         $user = new User();
//         $password = $this->hasher->hashPassword($user, '123456789Pj');
//         $user->setUsername("pj")
//    ->setPassword($password)
//    ->setEmail("truc@truc.fr")
//    ->setRoles($user->getRoles());
//         $buildings = Array();
//         for ($i = 0; $i < 4; $i++) {
//             $buildings[$i] = new Building();
//             $buildings[$i]->setName($faker->streetAddress);
//             $buildings[$i]->setUser($user);
//             for ($k = 0; $k < 2; $k++) {
//                 if ($k == 0 && $i == 0){
//                     $stations[$k] = new Station;
//                     $stations[$k]
//                         ->setName($faker->country)
//                         ->setBuilding($buildings[$i])
//                         ->setActivationDate(\DateTime::createFromFormat('U.u', sprintf('%f', $t)))
//                         ->setState(1)
//                         ->setMac("D4:8A:FC:A7:76:FC");

//                     $manager->persist($stations[$k]);
//                         } else {
//                             $stations[$k] = new Station;
//                             $stations[$k]->setName($faker->country);
//                             $stations[$k]->setBuilding($buildings[$i]);
//                             $stations[$k]->setActivationDate(\DateTime::createFromFormat('U.u', sprintf('%f', $t)));
//                             $stations[$k]->setMac($faker->macAddress);
//                             $stations[$k]->setState(0);

//                             $manager->persist($stations[$k]);
//                         }
//                }
//                $manager->persist($buildings[$i]);
//            }
//         $manager->persist($user);

//            $auteurs = Array();
//            for ($i = 0; $i < 4; $i++) {
//                $auteurs[$i] = new User();
//                $password = $this->hasher->hashPassword($auteurs[$i], $faker->password);
//                $auteurs[$i]->setUsername($faker->userName);
//                $auteurs[$i]->setEmail($faker->email);
//                $auteurs[$i]->setPassword($password);
//                $auteurs[$i]->setRoles($auteurs[$i]->getRoles());

//                $manager->persist($auteurs[$i]);
//            }

//         $manager->flush();
//     }

// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Building;
use App\Entity\Station;
use App\Entity\Reading;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        ini_set('memory_limit', '512M');

        // Create a user
        $user = new User();
        $user->setUsername('username');
        $user->setEmail('pj@pj.fr');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, '123456789Pj'));

        // Create a building
        $building = new Building();
        $building->setName('Building 1');
        $building->setUser($user);

        // Create a station
        $station = new Station();
        $station->setState(1);
        $station->setName('Station 1');
        $station->setActivationDate(new \DateTime());
        $station->setBuilding($building);
        $station->setMac('00:1B:44:11:3A:B7');

        // Persist user, building, and station
        $manager->persist($user);
        $manager->persist($building);
        $manager->persist($station);
        $manager->flush();

        // Add readings in batches to avoid memory issues
        $startDate = new \DateTime('-1 year');
        $endDate = new \DateTime();
        $interval = new \DateInterval('PT30M'); // 30 minutes interval

        $datePeriod = new \DatePeriod($startDate, $interval, $endDate);
        $batchSize = 100;
        $i = 0;

        foreach ($datePeriod as $date) {
            $reading = new Reading();
            $reading->setDate($date);
            $reading->setTemperature(rand(10, 35));
            $reading->setHumidity(rand(30, 80));
            $reading->setAltitude(331);
            $reading->setPressure(930);
            $reading->setStation($station);
            $manager->persist($reading);

            if (($i % $batchSize) === 0) {
                $manager->flush();
                $manager->clear(Reading::class);
            }
            $i++;
        }

        $manager->flush();
        $manager->clear();
    }
}


// }
