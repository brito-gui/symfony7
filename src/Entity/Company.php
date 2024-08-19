<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    forceEager: false,
    normalizationContext: ['groups' => ['company:read']],
    denormalizationContext: ['groups' => ['company:write']]
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ApiProperty(identifier:false)]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty(identifier:true)]
    #[Groups(['company:read','company:write', 'subCompany:read', 'user:read','account:read'])]
    #[ORM\Column(type:"uuid", unique:true)]
    private ?UuidInterface $uuid = null;

    #[Groups(['company:read','company:write', 'subCompany:read', 'user:read','account:read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, SubCompany>
     */
    #[ORM\OneToMany(targetEntity: SubCompany::class, mappedBy: 'company', orphanRemoval: true, cascade:['persist', 'remove', 'refresh', 'detach'])]
    #[Groups(['company:read','company:write'])]
    private Collection $subCompanies;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'company')]
    private Collection $accounts;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->subCompanies = new ArrayCollection();
        $this->accounts = new ArrayCollection();
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

    /**
     * @return Collection<int, SubCompany>
     */
    public function getSubCompanies(): Collection
    {
        return $this->subCompanies;
    }

    public function addSubCompany(SubCompany $subCompany): static
    {
        if (!$this->subCompanies->contains($subCompany)) {
            $this->subCompanies->add($subCompany);
            $subCompany->setCompany($this);
        }

        return $this;
    }

    public function removeSubCompany(SubCompany $subCompany): static
    {
        if ($this->subCompanies->removeElement($subCompany)) {
            // set the owning side to null (unless already changed)
            if ($subCompany->getCompany() === $this) {
                $subCompany->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(Account $account): static
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
            $account->setCompany($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): static
    {
        if ($this->accounts->removeElement($account)) {
            // set the owning side to null (unless already changed)
            if ($account->getCompany() === $this) {
                $account->setCompany(null);
            }
        }

        return $this;
    }
}
