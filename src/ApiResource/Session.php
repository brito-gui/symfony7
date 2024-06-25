<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Controller\SessionController;
use App\Entity\SubCompany;
use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ApiResource(
    operations: [
        new Patch(
            name: 'patch',
            uriTemplate: '/session',
            controller: SessionController::class,
            denormalizationContext: ['groups' => ['session:write']],
            normalizationContext: ['groups' => ['session:read']]
        )
    ],
    normalizationContext: ['groups' => ['session:read']],
    denormalizationContext: ['groups' => ['session:write']],
)]
class Session
{

    #[Groups(['role:read'])]
    private ?UuidInterface $uuid = null;

    #[Groups(['session:write'])]
    private ?SubCompany $subCompany = null;

    #[Groups(['session:read'])]
    private string $token;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getSubCompany(): ?SubCompany
    {
        return $this->subCompany;
    }

    public function setSubCompany(?SubCompany $subCompany): static
    {
        $this->subCompany = $subCompany;

        return $this;
    }
}
