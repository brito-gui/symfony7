<?php

namespace App\Tests\Funcional\Controller;

use App\Entity\User;
use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Zenstruck\Foundry\Test\Factories;

use function Zenstruck\Foundry\Persistence\repository;

class SessionControllerTest extends AbstractApiTestCase
{
    use Factories, ReloadDatabaseTrait;

    public function testAssuresUserWillSwitchSubCompanySession()
    {
        // arrange
        $client = self::createClient();
        UserFactory::new()->asMultiSubCompanyUser(3)->create();
        $userRepository = repository(User::class);
        $user = $userRepository->last();

        // retrieve a token
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $user->getEmail(),
                'password' => '123456',
            ],
        ]);

        $json = $response->toArray();
        $oldToken = $json['token'];

        // test authorized
        $response = $client->request('PATCH', '/api/session', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'auth_bearer' => $oldToken,
            'json' => ['sub_company' => ['uuid' => $user->getUserRoles()->last()->getSubCompany()->getUuid()]]
        ]);

        // Assures route responses a success code
        $this->assertResponseIsSuccessful();

        $json = $response->toArray();
        $newToken = $json['token'];

        // test authorized with new token
        $client->request('GET', '/api/users', [
            'auth_bearer' => $newToken,
        ]);

        $this->assertResponseIsSuccessful();

        // Assures the first subCompany is the same as default subCompany
        $this->assertSame(
            $user->getUserRoles()->first()->getSubCompany()->getUuid(),
            $user->getDefaultSubCompany()->getUuid()
        );
        // Assures the new subCompany is not the same as default subCompany
        $this->assertNotSame($oldToken, $newToken);
    }

    /**
     * @param  User $user
     * @param  int  $expectedStatusCode
     *
     * @return void
     */
    public function testAssuresAdminsCantSwitchSubCompanySession()
    {
        // arrange
        $client = self::createClient();
        UserFactory::new()->asAdmin()->create();
        $userRepository = repository(User::class);
        $user = $userRepository->last();

        // retrieve a token
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $user->getEmail(),
                'password' => '123456',
            ],
        ]);

        $json = $response->toArray();

        // test authorized
        $response = $client->request('PATCH', '/api/session', [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'auth_bearer' =>  $json['token'],
            'json' => ['sub_company' => ['uuid' => '3fa85f64-5717-4562-b3fc-2c963f66afa6']]
        ]);

        // Assures route responses a 404 code
        $this->assertResponseStatusCodeSame(404);
    }
}
