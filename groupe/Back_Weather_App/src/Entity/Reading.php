<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\ReadingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReadingRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Post(),
    ]
)]
class Reading
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reading'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['reading'])]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['reading'])]
    private ?float $temperature = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['reading'])]
    private ?int $humidity = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['reading'])]
    private ?float $altitude = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['reading'])]
    private ?float $pressure = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Station", inversedBy: "readings")]
    #[ORM\JoinColumn(nullable: false)]
    private $station;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getHumidity(): ?int
    {
        return $this->humidity;
    }

    public function setHumidity(int $humidity): static
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    public function setAltitude(?int $altitude): static
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getPressure(): ?int
    {
        return $this->pressure;
    }

    public function setPressure(?int $pressure): static
    {
        $this->pressure = $pressure;

        return $this;
    }

    public function setStation(?Station $station): self
    {
        $this->station = $station;

        return $this;
    }
}
