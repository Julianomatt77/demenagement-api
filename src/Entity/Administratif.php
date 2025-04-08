<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AdministratifController;
use App\Repository\AdministratifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdministratifRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/administratif', name: 'get_all_administratif'),
        new Post(uriTemplate: '/api/administratif', denormalizationContext: ['groups' => ['administratif:write']], name: 'add_administratif'),
        new get(uriTemplate: '/api/administratif/{id}', denormalizationContext: ['groups' => ['administratif:read']], name: 'get_administratif'),
        new Patch(uriTemplate: '/api/administratif/{id}', denormalizationContext: ['groups' => ['administratif:write']], name: 'edit_administratif'),
        new Delete(uriTemplate: '/api/administratif/{id}', denormalizationContext: ['groups' => ['administratif:read']], name: 'delete_administratif'),
    ],
    formats: ['json'],
    controller: AdministratifController::class,
)]
class Administratif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'administratif:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['administratif:read', 'administratif:write'])]
    private ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['administratif:read', 'administratif:write'])]
    private ?string $assigned_user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['administratif:read', 'administratif:write'])]
    private ?\DateTimeInterface $date_created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['administratif:read', 'administratif:write'])]
    private ?\DateTimeInterface $date_done = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['administratif:read', 'administratif:write'])]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['administratif:read'])]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne(inversedBy: 'administratifs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['administratif:read'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getAssignedUser(): ?string
    {
        return $this->assigned_user;
    }

    public function setAssignedUser(?string $assigned_user): static
    {
        $this->assigned_user = $assigned_user;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(?\DateTimeInterface $date_created): static
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getDateDone(): ?\DateTimeInterface
    {
        return $this->date_done;
    }

    public function setDateDone(?\DateTimeInterface $date_done): static
    {
        $this->date_done = $date_done;

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
}
