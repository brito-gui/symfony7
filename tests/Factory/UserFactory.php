<?php

namespace App\Tests\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    private $passwordHasher;
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->passwordHasher = $passwordHasher;
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $company = LazyValue::memoize(fn() => CompanyFactory::createOne());

        return [
            'email' => self::faker()->unique()->safeEmail(),
            'password' => '123456',
            'profile' => User::PROFILE_USER,
            'userRoles' => LazyValue::memoize(fn() => UserRoleFactory::new()->with(["subCompany" => SubCompanyFactory::new(['company' => $company])])->many(1)),
        ];
    }

    /**
     * @return self
     */
    public function asSuperAdmin(): self
    {
        return $this->with(
            [
                'profile' => User::PROFILE_SUPERADMIN,
                'userRoles' => [],
            ]
        );
    }

    /**
     * asAdmin
     *
     * @return self
     */
    public function asAdmin(): self
    {
        return $this->with(
            [
                'profile' => User::PROFILE_ADMIN,
                'userRoles' => UserRoleFactory::new(
                    [
                        'company' => CompanyFactory::new(['name' => 'ADMIN Company']),
                        'role' => RoleFactory::new(['title' => 'ROLE_ADMIN', 'permissions' => ['*']]),
                        'subCompany' => null,
                    ]
                )->many(1)
            ]
        );
    }

    /**
     * asMultiSubCompanyUser
     *
     * @param  int $howMany
     *
     * @return self
     */
    public function asMultiSubCompanyUser(int $howMany = 2): self
    {
        $company = LazyValue::memoize(fn() => CompanyFactory::createOne());

        return $this->with(
            [
                'profile' => User::PROFILE_USER,
                'userRoles' => UserRoleFactory::new()
                    ->with(
                        [
                            "subCompany" => SubCompanyFactory::new(['company' => $company])
                        ]
                    )->many($howMany),
            ]
        );
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
            })
        ;
    }
}
