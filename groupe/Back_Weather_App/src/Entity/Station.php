<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\StationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new Patch(),
        new Delete()
    ]
)]
class Station
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['station'])]
    private ?int $id = null;

    #[ORM\Column(nullable: false, type: Types::INTEGER)] //0 => eteint/deco, 1 => fonctionnel/connecté, 2 => defectueux
    #[Groups(['station'])]
    private ?int $state = 0;

    #[ORM\Column(nullable: true, length: 255)]
    #[Groups(['station'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true, type: Types::DATETIME_MUTABLE)]
    #[Groups(['station'])]
    private ?\DateTimeInterface $activationDate = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Building", inversedBy: "stations")]
    #[ORM\JoinColumn(nullable: true)]
    private $building;

    #[ORM\OneToMany(targetEntity: "App\Entity\Reading", mappedBy: "station", cascade: ["remove"])]
    private $readings;

    #[ORM\Column(length: 17, unique: true)]
    #[Groups(['station'])]
    private ?string $mac = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getActivationDate(): ?\DateTimeInterface
    {
        return $this->activationDate;
    }

    public function setActivationDate(?\DateTimeInterface $activationDate): static
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    public function setBuilding(?Building $building): self
    {
        $this->building = $building;
        return $this;
    }

    public function getMac(): ?string
    {
        return $this->mac;
    }

    public function setMac(string $mac): static
    {
        $this->mac = $mac;

        return $this;
    }

    public function getReadings(): Collection
    {
        return $this->readings;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): static
    {
        $this->state = $state;

        return $this;
    }
}
