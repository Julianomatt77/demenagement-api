<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\ElementController;
use App\Repository\ElementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ElementRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/elements', paginationEnabled: false, name: 'get_all_elements'),
        new Post(uriTemplate: '/api/elements', denormalizationContext: ['groups' => ['element:write']], name: 'add_element'),
        new get(uriTemplate: '/api/elements/{id}', denormalizationContext: ['groups' => ['element:read']], name: 'get_element'),
        new Patch(uriTemplate: '/api/elements/{id}', denormalizationContext: ['groups' => ['element:write']], name: 'edit_element'),
        new Delete(uriTemplate: '/api/elements/{id}', denormalizationContext: ['groups' => ['element:read']], name: 'delete_element'),
    ],
    formats: ['json'],
    controller: ElementController::class,
)]
class Element
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['carton:read', 'user:read', 'room:read', 'element:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['carton:read','room:read', 'element:read', 'element:write'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['carton:read', 'room:read', 'element:read', 'element:write'])]
    private ?bool $in_box = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['carton:read', 'room:read', 'element:read', 'element:write'])]
    private ?bool $out_box = null;

    #[ORM\ManyToOne(inversedBy: 'elements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['element:read', 'element:write'])]
    private ?Carton $carton = null;

    #[ORM\ManyToOne(inversedBy: 'elements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['element:read'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['element:read'])]
    private ?\DateTimeImmutable $deleted_at = null;

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

    public function isInBox(): ?bool
    {
        return $this->in_box;
    }

    public function setInBox(?bool $in_box): static
    {
        $this->in_box = $in_box;

        return $this;
    }

    public function isOutBox(): ?bool
    {
        return $this->out_box;
    }

    public function setOutBox(?bool $out_box): static
    {
        $this->out_box = $out_box;

        return $this;
    }

    public function getCarton(): ?Carton
    {
        return $this->carton;
    }

    public function setCarton(?Carton $carton): static
    {
        $this->carton = $carton;

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
}
