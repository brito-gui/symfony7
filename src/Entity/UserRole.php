<?php

namespace App\Entity;

use App\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
class UserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read'])]
    private ?string $profile = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read','user:write'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'userRoles')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user:read','user:write'])]
    private ?SubCompany $subCompany = null;

    #[ORM\ManyToOne(inversedBy: 'userRoles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read','user:write'])]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read','user:write'])]
    private ?Role $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function getProfile(): string
    {
        return $this->user->getProfile();
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getSubCompany(): ?SubCompany
    {
        return $this->subCompany;
    }

    public function setSubCompany(?SubCompany $subCompany): static
    {
        $this->subCompany = $subCompany;
        if (!is_null($subCompany)){
            $this->setCompany($this->subCompany->getCompany());
        }

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

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }
}
