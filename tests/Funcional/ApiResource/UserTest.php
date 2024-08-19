<?php

namespace App\Tests\Funcional\ApiResource;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\SubCompanyFactory;
use App\Tests\Factory\UserFactory;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserTest extends AbstractApiTestCase
{
    use ReloadDatabaseTrait;

    private UserRepository $userRepository;

    public function setUp():void
    {
        parent::setUp();

        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testCreateUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $response = $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@example.com',
                'profile' => User::PROFILE_SUPERADMIN,
                'plainPassword' => '123456',
            ],
        ]);

        $json = $response->toArray();

        /**
         * @var UserRepository
         */
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['uuid' => $json['uuid']]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => sprintf("/api/users/%s", $user->getUuid())]);
    }

    /**
     * testAssuresUserWillFetchOnlyFromOwnSubCompany
     *
     * @return void
     */
    /*public function testAssuresUserWillFetchOnlyFromOwnSubCompany()
    {
        // common client
        $client = $this->getStaticClient();

        // logged as superadmin client
        $superAdminClient = self::createAuthenticatedClient();

        $user = UserFactory::new()->asMultiSubCompanyUser(3)->create(['email' => 'user@example.com'])->_real();

        $subCompany = $user->getDefaultSubCompany();

        $jwt = $this->getTokenByEmail($user->getEmail());

        // Creating 10 Accounts for the multicompany user
        AccountFactory::createMany(10, ['subCompany' => $subCompany, 'company' => $subCompany->getCompany()]);

        // Creating 50 Accounts for the multicompany user
        AccountFactory::createMany(50);

        $response = $client->request('GET', '/api/accounts', [
            'auth_bearer' => $jwt,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $json = $response->toArray();

        $this->assertCount(10, $json);

        $response = $superAdminClient->request('GET', '/api/accounts', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $json = $response->toArray();

        // Retrieving only 30 due the pagination
        $this->assertCount(30, $json);
    }*/

    /**
     * testAssuresUserWillFetchOnlyFromOwnCompany
     *
     * @return void
     */
    /*public function testAssuresUserWillFetchOnlyFromOwnCompany()
    {
        // common client
        $client = self::createClient();

        // logged as superadmin client
        $superAdminClient = self::createAuthenticatedClient();

        $user = UserFactory::new()->asAdmin()->create()->_real();

        $company = $user->getCompany();

        $jwt = $this->getTokenByEmail($user->getEmail());

        $subCompany = SubCompanyFactory::new()->createOne(['company' => $company])->_real();

        // Creating 10 Accounts for the multicompany user
        AccountFactory::createMany(10, ['subCompany' => $subCompany, 'company' => $company]);

        // Creating 50 Accounts for the multicompany user
        AccountFactory::createMany(50);

        $response = $client->request('GET', '/api/accounts', [
            'auth_bearer' => $jwt,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $json = $response->toArray();
        $this->assertCount(10, $json);

        $response = $superAdminClient->request('GET', '/api/accounts', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $json = $response->toArray();

        // Retrieving only 30 due the pagination
        $this->assertCount(30, $json);
    }*/

    /**
     * provideDataForCheckIfTheDoctrineExtensionisWorking
     *
     * @return void
     */
    public function provideDataForCheckIfTheDoctrineExtensionisWorking()
    {
        return [
            'Fetching accounts from users subcompany' => [
                'jwt' => $this->getUserByEmail('user@example.com'),
            ],
            'Fetching accounts from admin' => [
                'jwt' => $this->getUserByEmail('admin@example.com'),
            ]
        ];
    }

    public function mockUsersAndAccounts(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'admin@example.com'])->_real();

        $subCompany = SubCompanyFactory::new()->createOne(['company' => $admin->getCompany()])->_real();

        // Creating 10 Accounts for the admin user
        AccountFactory::createMany(10, ['subCompany' => $subCompany, 'company' => $admin->getCompany()]);

        $user = UserFactory::new()->asMultiSubCompanyUser(3)->create(['email' => 'user@example.com'])->_real();

        // Creating 10 Accounts for the multicompany user
        AccountFactory::createMany(10, ['subCompany' => $user->getDefaultSubCompany(), 'company' => $subCompany->getCompany()]);

        // Creating 50 Accounts
        AccountFactory::createMany(50);

        //dd($this->userRepository->findAll());

    }

    /**
     * getUserByEmail
     *
     * @param  mixed $email
     * @return User
     */
    public function getUserByEmail($email): User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
