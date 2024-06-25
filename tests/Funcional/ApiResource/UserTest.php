<?php

namespace App\Tests\Funcional\ApiResource;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AbstractApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserTest extends AbstractApiTestCase
{
    use ReloadDatabaseTrait;

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
}
