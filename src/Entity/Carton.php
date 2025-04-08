<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\CartonController;
use App\Repository\CartonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartonRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/cartons', name: 'get_all_cartons'),
        new Post(uriTemplate: '/api/cartons', denormalizationContext: ['groups' => ['carton:write']], name: 'add_carton'),
        new get(uriTemplate: '/api/cartons/{id}', denormalizationContext: ['groups' => ['carton:read']], name: 'get_carton'),
        new Patch(uriTemplate: '/api/cartons/{id}', denormalizationContext: ['groups' => ['carton:write']], name: 'edit_carton'),
        new Delete(uriTemplate: '/api/cartons/{id}', denormalizationContext: ['groups' => ['carton:read']], name: 'delete_carton'),
    ],
    formats: ['json'],
    controller: CartonController::class,
)]
class Carton
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['carton:read', 'user:read', 'room:read', 'element:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['carton:read', 'carton:write', 'user:read', 'room:read', 'element:read'])]
    private ?int $numero = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['carton:read', 'carton:write', 'room:read', 'element:read'])]
    private ?bool $filled = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['carton:read', 'carton:write', 'room:read', 'element:read'])]
    private ?bool $items_removed = null;

    #[ORM\ManyToOne(inversedBy: 'cartons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['carton:read', 'carton:write', 'element:read'])]
    private ?Room $room = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['carton:read', 'room:read', 'element:read'])]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne(inversedBy: 'cartons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['carton:read'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Element>
     */
    #[ORM\OneToMany(targetEntity: Element::class, mappedBy: 'carton')]
    #[Groups(['carton:read'])]
    private Collection $elements;

    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function isFilled(): ?bool
    {
        return $this->filled;
    }

    public function setFilled(?bool $filled): static
    {
        $this->filled = $filled;

        return $this;
    }

    public function isItemsRemoved(): ?bool
    {
        return $this->items_removed;
    }

    public function setItemsRemoved(?bool $items_removed): static
    {
        $this->items_removed = $items_removed;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Element>
     */
    public function getElements(): Collection
    {
        return $this->elements;
    }

    public function addElement(Element $element): static
    {
        if (!$this->elements->contains($element)) {
            $this->elements->add($element);
            $element->setCarton($this);
        }

        return $this;
    }

    public function removeElement(Element $element): static
    {
        if ($this->elements->removeElement($element)) {
            // set the owning side to null (unless already changed)
            if ($element->getCarton() === $this) {
                $element->setCarton(null);
            }
        }

        return $this;
    }
}
