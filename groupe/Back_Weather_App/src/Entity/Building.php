<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\BuildingRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new Delete()
    ]
)]
class Building
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['building'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['building'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "buildings")]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\OneToMany(targetEntity: "App\Entity\Station", mappedBy: "building", cascade: ["remove"])]
    private $stations;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStations(): Collection
    {
        return $this->stations;
    }
}
