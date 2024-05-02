<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const PROFILE_SUPERADMIN = 'SUPERADMIN';
    const PROFILE_ADMIN = 'ADMIN';
    const PROFILE_USER = 'USER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier:false)]
    private ?int $id = null;

    #[ORM\Column(type:"uuid", unique:true)]
    #[ApiProperty(identifier:true)]
    #[Groups(['user:read'])]
    private ?UuidInterface $uuid = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?string $password = null;

    /**
     * @var ?string The plaintext password
     */
    #[Groups(['user:write'])]
    private $plainPassword = null;

    #[ORM\Column(length: 10)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $profile = null;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'user', orphanRemoval: true, cascade:['persist', 'remove', 'refresh', 'detach'])]
    #[Groups(['user:read','user:write'])]
    private Collection $userRoles;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUserRoles(): Collection
    {
        if ($this->profile === self::PROFILE_SUPERADMIN) {
            $userRole = new UserRole();
            $role = new Role();
            $role->setTitle("ROLE_SUPERADMIN");
            $role->setPermissions(["*"]);
            $userRole->setRole($role);
            $userRole->setUser($this);
            return new ArrayCollection([$userRole]);
        }
        return $this->userRoles;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;

        return $this;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(string $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUser($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): static
    {
        if ($this->userRoles->removeElement($userRole)) {
            // set the owning side to null (unless already changed)
            if ($userRole->getUser() === $this) {
                $userRole->setUser(null);
            }
        }

        return $this;
    }
}
