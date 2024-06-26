<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Company;
use App\Entity\Role;
use App\Entity\SubCompany;
use App\Entity\User;
use App\Security\User as SecurityUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {}

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * {@inheritdoc}
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $resourceClass
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass)
    {
        /**
        * @var SecurityUser $user
        */
        if (($this->isValidResource($resourceClass)) || (null === $user = $this->security->getUser()) || ($user->getProfile() === User::PROFILE_SUPERADMIN)) {
            return;
        }

        $subCompanyId = $user->getSubCompanyId();
        $companyId = $user->getCompanyId();

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.companyId = :companyId', $rootAlias));
        $queryBuilder->setParameter('companyId', $companyId);

        if ($subCompanyId > 0) {
            $queryBuilder->andWhere(sprintf('%s.subCompanyId = :subCompanyId', $rootAlias));
            $queryBuilder->setParameter('subCompanyId', $subCompanyId);
        }
    }

    /**
     * @param  string $resourceClass
     *
     * @return bool
     */
    private function isValidResource(string $resourceClass): bool
    {
        return in_array($resourceClass, [Company::class, Role::class, SubCompany::class, User::class]);
    }
}