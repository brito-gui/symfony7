<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['role:read']],
    denormalizationContext: ['groups' => ['role:write']],
)]

class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier:false)]
    private ?int $id = null;

    #[ORM\Column(type:"uuid", unique:true)]
    #[ApiProperty(identifier:true)]
    #[Groups(['role:read'])]
    private ?UuidInterface $uuid = null;

    #[Groups(['role:read','role:write', 'user:read'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['role:read','role:write', 'user:read'])]
    private array $permissions = [];

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): static
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
