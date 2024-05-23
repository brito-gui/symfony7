<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SubCompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

#[ORM\Entity(repositoryClass: SubCompanyRepository::class)]
#[ApiResource(
    forceEager: false,
    normalizationContext: ['groups' => ['subCompany:read']],
    denormalizationContext: ['groups' => ['subCompany:write']],
)]
class SubCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier:false)]
    private ?int $id = null;

    #[ApiProperty(identifier:true)]
    #[Groups(['company:read', 'subCompany:write', 'subCompany:read', 'user:read'])]
    #[ORM\Column(type: 'uuid', unique:true)]
    private ?UuidInterface $uuid = null;

    #[Groups(['subCompany:read', 'subCompany:write', 'company:read', 'user:read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'subCompanies', cascade:['persist', 'remove', 'refresh', 'detach'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['subCompany:read', 'subCompany:write', 'user:read'])]
    private ?Company $company = null;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'subCompany')]
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

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setSubCompany($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): static
    {
        if ($this->userRoles->removeElement($userRole)) {
            // set the owning side to null (unless already changed)
            if ($userRole->getSubCompany() === $this) {
                $userRole->setSubCompany(null);
            }
        }

        return $this;
    }
}
