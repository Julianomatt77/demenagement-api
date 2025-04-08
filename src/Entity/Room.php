<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\RoomController;
use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/rooms', name: 'get_all_rooms'),
        new Post(uriTemplate: '/api/rooms', denormalizationContext: ['groups' => ['room:write']], name: 'add_room'),
        new get(uriTemplate: '/api/rooms/{id}', denormalizationContext: ['groups' => ['room:read']], name: 'get_room'),
        new Patch(uriTemplate: '/api/rooms/{id}', denormalizationContext: ['groups' => ['room:write']], name: 'edit_room'),
        new Delete(uriTemplate: '/api/rooms/{id}', denormalizationContext: ['groups' => ['room:read']], name: 'delete_room'),
    ],
    formats: ["json"],
    controller: RoomController::class,
)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room:read', 'user:read', 'carton:read', 'element:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['room:read', 'room:write', 'user:read', 'carton:read', 'element:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['room:read', 'room:write', 'user:read', 'carton:read'])]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['room:read'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['room:read'])]
    private ?\DateTimeImmutable $deleted_at = null;

    /**
     * @var Collection<int, Carton>
     */
    #[ORM\OneToMany(targetEntity: Carton::class, mappedBy: 'room')]
    #[Groups(['room:read'])]
    private Collection $cartons;

    public function __construct()
    {
        $this->cartons = new ArrayCollection();
    }

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    /**
     * @return Collection<int, Carton>
     */
    public function getCartons(): Collection
    {
        return $this->cartons;
    }

    public function addCarton(Carton $carton): static
    {
        if (!$this->cartons->contains($carton)) {
            $this->cartons->add($carton);
            $carton->setRoom($this);
        }

        return $this;
    }

    public function removeCarton(Carton $carton): static
    {
        if ($this->cartons->removeElement($carton)) {
            // set the owning side to null (unless already changed)
            if ($carton->getRoom() === $this) {
                $carton->setRoom(null);
            }
        }

        return $this;
    }
}
