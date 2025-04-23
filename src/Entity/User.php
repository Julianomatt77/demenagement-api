<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Controller\PasswordResetController;
use App\Controller\UserController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/api/users-infos',
            controller: UserController::class,
            openapi: new Operation(
                summary: 'Get user informations',
                description: 'Get user informations.'),
            denormalizationContext: ['groups' => ['user:read']],
            name: 'app_user_show'),
        new Post(
            uriTemplate: '/api/register',
            controller: UserController::class,
            openapi: new Operation(
                summary: 'User registration',
                description: 'Create an user account.'),
            denormalizationContext: ['groups' => ['user:write']],
            name: 'api_register'),
        new Delete(
            uriTemplate: '/api/user-delete',
            controller: UserController::class,
            openapi: new Operation(
                summary: 'Delete an user account',
                description: 'Delete an user account.'),
            denormalizationContext: ['groups' => ['user:write']],
            name: 'app_user_delete'),
        new Patch(
            uriTemplate: '/api/user-email-update',
            controller: UserController::class,
            openapi: new Operation(
                summary: 'Update your email address.',
                description: 'Update your email address.'),
            denormalizationContext: ['groups' => ['user:write']],
            name: 'user_email_update'),
        new Post(
            uriTemplate: '/api/password/forgot',
            controller: PasswordResetController::class,
            openapi: new Operation(
                summary: 'Send an email to reset the password',
                description: 'Send an email to reset the password.'),
            denormalizationContext: ['groups' => ['email:write']],
            name: 'password_forgot'),
        new Patch(
            uriTemplate: '/api/password/reset/{token}',
            controller: PasswordResetController::class,
            openapi: new Operation(
                summary: 'Reset the user password with a token',
                description: 'Reset the user password with a token.'),
            denormalizationContext: ['groups' => ['password:write']],
            name: 'password_reset'),
        new Patch(
            uriTemplate: '/api/password/update',
            controller: PasswordResetController::class,
            openapi: new Operation(
                summary: 'Update the user password',
                description: 'Update the user password.'),
            denormalizationContext: ['groups' => ['password:write']],
            name: 'password_update'),
    ],
    formats: ["json"],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'demenageur:read', 'carton:read', 'element:read', 'room:read', 'administratif:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:write', 'user:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:write', 'user:read', 'email:write'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write', 'password:write'])]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $token = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $registeredAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $tokenExpiryDate = null;

    /**
     * @var Collection<int, Demenageur>
     */
    #[ORM\OneToMany(targetEntity: Demenageur::class, mappedBy: 'user')]
    private Collection $demenageurs;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\OneToMany(targetEntity: Room::class, mappedBy: 'user')]
    private Collection $rooms;

    /**
     * @var Collection<int, Carton>
     */
    #[ORM\OneToMany(targetEntity: Carton::class, mappedBy: 'user')]
    private Collection $cartons;

    /**
     * @var Collection<int, Element>
     */
    #[ORM\OneToMany(targetEntity: Element::class, mappedBy: 'user')]
    private Collection $elements;

    /**
     * @var Collection<int, Administratif>
     */
    #[ORM\OneToMany(targetEntity: Administratif::class, mappedBy: 'user')]
    private Collection $administratifs;

    public function __construct()
    {
        $this->demenageurs = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->cartons = new ArrayCollection();
        $this->elements = new ArrayCollection();
        $this->administratifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeImmutable|null $deletedAt
     */
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    /**
     * @param \DateTimeImmutable|null $registeredAt
     */
    public function setRegisteredAt(?\DateTimeImmutable $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTokenExpiryDate(): ?\DateTimeImmutable
    {
        return $this->tokenExpiryDate;
    }

    /**
     * @param \DateTimeImmutable|null $tokenExpiryDate
     */
    public function setTokenExpiryDate(?\DateTimeImmutable $tokenExpiryDate): void
    {
        $this->tokenExpiryDate = $tokenExpiryDate;
    }

    /**
     * @return Collection<int, Demenageur>
     */
    public function getDemenageurs(): Collection
    {
        return $this->demenageurs;
    }

    public function addDemenageur(Demenageur $demenageur): static
    {
        if (!$this->demenageurs->contains($demenageur)) {
            $this->demenageurs->add($demenageur);
            $demenageur->setUser($this);
        }

        return $this;
    }

    public function removeDemenageur(Demenageur $demenageur): static
    {
        if ($this->demenageurs->removeElement($demenageur)) {
            // set the owning side to null (unless already changed)
            if ($demenageur->getUser() === $this) {
                $demenageur->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setUser($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getUser() === $this) {
                $room->setUser(null);
            }
        }

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
            $carton->setUser($this);
        }

        return $this;
    }

    public function removeCarton(Carton $carton): static
    {
        if ($this->cartons->removeElement($carton)) {
            // set the owning side to null (unless already changed)
            if ($carton->getUser() === $this) {
                $carton->setUser(null);
            }
        }

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
            $element->setUser($this);
        }

        return $this;
    }

    public function removeElement(Element $element): static
    {
        if ($this->elements->removeElement($element)) {
            // set the owning side to null (unless already changed)
            if ($element->getUser() === $this) {
                $element->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Administratif>
     */
    public function getAdministratifs(): Collection
    {
        return $this->administratifs;
    }

    public function addAdministratif(Administratif $administratif): static
    {
        if (!$this->administratifs->contains($administratif)) {
            $this->administratifs->add($administratif);
            $administratif->setUser($this);
        }

        return $this;
    }

    public function removeAdministratif(Administratif $administratif): static
    {
        if ($this->administratifs->removeElement($administratif)) {
            // set the owning side to null (unless already changed)
            if ($administratif->getUser() === $this) {
                $administratif->setUser(null);
            }
        }

        return $this;
    }


}
