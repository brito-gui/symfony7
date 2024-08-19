<?php

namespace App\Tests\Funcional\ApiResource;

use App\Repository\AccountRepository;
use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\SubCompanyFactory;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class AccountTest extends AbstractApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateAccount(): void
    {
        $client = $this->createAuthenticatedClient();

        $subCompany = SubCompanyFactory::createOne()->_real();

        $response = $client->request('POST', '/api/accounts', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'number' => '3.1.1.001.000001',
                'description' => 'VENDA VN PASSAGEIROS',
                'subCompany' => sprintf("/api/sub_companies/%s", $subCompany->getUuid()),
                'company' => sprintf("/api/companies/%s", $subCompany->getCompany()->getUuid()),
            ],
        ]);

        $json = $response->toArray();

        /**
         * @var AccountRepository
         */
        $repository = self::getContainer()->get(AccountRepository::class);
        $account = $repository->findOneBy(['uuid' => $json['uuid']]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => sprintf("/api/accounts/%s", $account->getUuid())]);
    }

}
