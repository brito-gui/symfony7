<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

abstract class AbstractApiTestCase extends ApiTestCase
{
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
        $user->setPassword(
            $container->get('security.user_password_hasher')->hashPassword($user, '$3CR3T')
        );

        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        // retrieve a token
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'root@example.com',
                'password' => '$3CR3T',
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

        //dd($encoder->encode($claims));

        return $client;
    }
}