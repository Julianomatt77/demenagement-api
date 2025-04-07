<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\DemenageurController;
use App\Repository\DemenageurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DemenageurRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/demenageurs', name: 'get_all_demenageurs'),
        new Post(uriTemplate: '/api/demenageurs', denormalizationContext: ['groups' => ['demenageur:write']], name: 'add_demenageur'),
        new get(uriTemplate: '/api/demenageurs/{id}', denormalizationContext: ['groups' => ['demenageur:read']], name: 'get_demenageur'),
        new Patch(uriTemplate: '/api/demenageurs/{id}', denormalizationContext: ['groups' => ['demenageur:write']], name: 'edit_demenageur'),
        new Delete(uriTemplate: '/api/demenageurs/{id}', denormalizationContext: ['groups' => ['demenageur:read']], name: 'delete_demenageur'),
    ],
    formats: ["json"],
    controller: DemenageurController::class,
)]
class Demenageur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['demenageur:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['demenageur:read', 'demenageur:write'])]
    private ?string $name = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(nullable: true)]
    private ?string $phone = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $devis_reference = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $devis_price = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $paid = null;

    #[Groups(['demenageur:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $left_to_paid = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $devis_date = null;

    #[Groups(['demenageur:read', 'demenageur:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'demenageurs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['demenageur:read'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['demenageur:read'])]
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDevisReference(): ?string
    {
        return $this->devis_reference;
    }

    public function setDevisReference(?string $devis_reference): static
    {
        $this->devis_reference = $devis_reference;

        return $this;
    }

    public function getDevisPrice(): ?int
    {
        return $this->devis_price;
    }

    public function setDevisPrice(?int $devis_price): static
    {
        $this->devis_price = $devis_price;

        return $this;
    }

    public function getPaid(): ?int
    {
        return $this->paid;
    }

    public function setPaid(?int $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getLeftToPaid(): ?int
    {
        return $this->left_to_paid;
    }

    public function setLeftToPaid(?int $left_to_paid): static
    {
        $this->left_to_paid = $left_to_paid;

        return $this;
    }

    public function getDevisDate(): ?\DateTimeInterface
    {
        return $this->devis_date;
    }

    public function setDevisDate(?\DateTimeInterface $devis_date): static
    {
        $this->devis_date = $devis_date;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

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
