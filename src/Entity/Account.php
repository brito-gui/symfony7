<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ApiResource(
    forceEager: false,
    normalizationContext: ['groups' => ['account:read']],
    denormalizationContext: ['groups' => ['account:write']]
)]
class Account
{
    #[ApiProperty(identifier:false)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty(identifier:true)]
    #[Groups(['account:read','account:write'])]
    #[ORM\Column(type:"uuid", unique:true)]
    private ?UuidInterface $uuid = null;

    #[Groups(['account:read','account:write'])]
    #[ORM\Column(length: 255)]
    private ?string $number = null;

    #[Groups(['account:read','account:write'])]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[Groups(['account:read','account:write'])]
    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[Groups(['account:read','account:write'])]
    #[ORM\ManyToOne(inversedBy: 'accounts')]
    private ?SubCompany $subCompany = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
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

    /**
     * setSubCompany
     *
     * @param SubCompany|null $subCompany
     *
     * @return static
     */
    public function setSubCompany(?SubCompany $subCompany): static
    {
        $this->subCompany = $subCompany;
        if (!is_null($subCompany)){
            $this->setCompany($this->subCompany->getCompany());
        }

        return $this;
    }
}
