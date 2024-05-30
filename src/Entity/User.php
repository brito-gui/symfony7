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

    #[Groups(['user:read'])]
    private ?Company $company;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->userRoles = new ArrayCollection();
    }

    /**
     * getId
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * getEmail
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * setEmail
     *
     * @param  mixed $email
     * @return static
     */
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

    /**
     * getUserRoles
     *
     * @return Collection
     */
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
     * getRoles
     *
     * @return array
     */
    public function getRoles() : array
    {
        return [];
    }

    /**
     * @param SubCompany $subCompany
     *
     * @return UserRole|null
     */
    public function getUserRoleBySubCompany(SubCompany $subCompany): ?UserRole {
        $userRole = $this->getUserRoles()->filter(function (UserRole $userRole) use ($subCompany){
            return !is_null($userRole->getSubCompany()) && $userRole->getSubCompany()->getId() === $subCompany->getId();
        })->first();

        return !empty($userRole) ? $userRole : null;
    }

    /**
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        if ($this->getUserRoles()->isEmpty() || is_null($this->getUserRoles()->first()->getCompany())) {
            return null;
        }

        return $this->getUserRoles()->first()->getCompany();
    }

    /**
     * @return SubCompany
     */
    public function getDefaultSubCompany(): ?SubCompany
    {
        if ($this->getUserRoles()->isEmpty() || is_null($this->getUserRoles()->first()->getSubCompany())) {
            return null;
        }

        return $this->getUserRoles()->first()->getSubCompany();
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * setPassword
     *
     * @param  mixed $password
     * @return static
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * getPlainPassword
     *
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * setPlainPassword
     *
     * @param  mixed $plainPassword
     *
     * @return static
     */
    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;

        return $this;
    }

    /**
     * getUuid
     *
     * @return UuidInterface
     */
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

    /**
     * getProfile
     *
     * @return string
     */
    public function getProfile(): ?string
    {
        return $this->profile;
    }

    /**
     * setProfile
     *
     * @param  mixed $profile
     * @return static
     */
    public function setProfile(string $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * addUserRole
     *
     * @param  mixed $userRole
     * @return static
     */
    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUser($this);
        }

        return $this;
    }

    /**
     * removeUserRole
     *
     * @param  mixed $userRole
     * @return static
     */
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
