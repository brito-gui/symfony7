<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

abstract class AbstractApiTestCase extends ApiTestCase
{
    protected ?Client $client = null;

    public function getStaticClient(): Client
    {
        if(is_null($this->client)) {
            return $this->client = self::createClient();
        }

        return $this->client;
    }

    /**
     * createAuthenticatedClient
     *
     * @return Client
     */
    protected function createAuthenticatedClient(): Client
    {
        $client = self::createClient();
        $container = self::getContainer();

        $user = new User();
        $user->setEmail('root@example.com');
        $user->setProfile(User::PROFILE_SUPERADMIN);
        $user->setPassword(
            $container->get('security.user_password_hasher')->hashPassword($user, '123456')
        );

        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        // retrieve a token
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'root@example.com',
                'password' => '123456',
            ],
        ]);

        $json = $response->toArray();

        $client->setDefaultOptions([
            'auth_bearer' => $json['token'],
        ]);

        return $client;
    }

    /**
     * createAuthenticatedClient1
     *
     * @return Client
     */
    protected static function createAuthenticatedClient1(): Client
    {
        $client = self::createClient();
        $encoder = $client->getContainer()->get(JWTEncoderInterface::class);

        $client->setDefaultOptions([
            'auth_bearer' => $encoder->encode(["roles"=> ["ROLE_USER"], 'username' => "root@example.com"]),
        ]);

        return $client;
    }

    /**
     * getTokenByEmail
     *
     * @param  string $email
     *
     * @return void
     */
    protected function getTokenByEmail(string $email)
    {
        $response = $this->getStaticClient()->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $email,
                'password' => '123456',
            ],
        ]);

        return $response->toArray()['token'] ?? null;
    }
}