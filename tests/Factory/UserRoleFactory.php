<?php

namespace App\Tests\Factory;

use App\Entity\UserRole;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<UserRole>
 */
final class UserRoleFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return UserRole::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $title = self::faker()->randomElement(['ROLE_READ', 'ROLE_WRITE']);
        $permissions =[
            'ROLE_ADMIN' => ['*'],
            'ROLE_READ' => ['PATCH/api/session*','GET/api/companies*','GET/api/users*'],
            'ROLE_WRITE' => ['PATCH/api/session*','PUT/api/companies*','POST/api/companies*','PATCH/api/companies*','GET/api/users*'],
        ];
        $company = LazyValue::memoize(fn() => CompanyFactory::createOne());

        return [
            'subCompany' => SubCompanyFactory::new(['company' => $company]),
            'role' => RoleFactory::new(
                [
                    'title' => $title,
                    'permissions' => $permissions[$title],
                ]
            ),
            'user' => UserFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(UserRole $userRole): void {})
        ;
    }
}
