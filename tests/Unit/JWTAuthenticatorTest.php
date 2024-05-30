<?php
namespace App\Tests\Unit;

use App\Security\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JWTAuthenticatorTest extends TestCase
{
    /**
     * @var JWTAuthenticator $authenticator
     */
    private JWTAuthenticator $authenticator;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp():void
    {
        /**
         * @var JWTTokenManagerInterface&MockObject
         */
        $tokenManagerMock = $this->getMockBuilder(JWTTokenManagerInterface::class)->disableOriginalConstructor()->getMock();

        /**
         * @var EventDispatcherInterface&MockObject
         */
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)->disableOriginalConstructor()->getMock();
        /**
         * @var TokenExtractorInterface&MockObject
         */
        $tokenExtractorMock = $this->getMockBuilder(TokenExtractorInterface::class)->disableOriginalConstructor()->getMock();
        /**
         * @var UserProviderInterface
         */
        $userProviderMock = $this->getMockBuilder(UserProviderInterface::class)->disableOriginalConstructor()->getMock();

        $this->authenticator = new JWTAuthenticator(
            $tokenManagerMock,
            $eventDispatcherMock,
            $tokenExtractorMock,
            $userProviderMock,
            null
        );
    }

    /**
     * @dataProvider provideResourcesToBeValidated
     *
     * @return void
     */
    public function testAssureResourceWillBeValidated(string $method, string $pathInfo, array $allowedResources, bool $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->authenticator->checkPermissions(
                $method,
                $pathInfo,
                $allowedResources
            )
        );
    }

    /**
     * provideResourcesToBeValidated
     *
     * @return array
     */
    public function provideResourcesToBeValidated(): array
    {
        return [
            'allowed-resources-with-wildcard-at-beggining' => [
                'method' => 'GET',
                'pathInfo' => '/api/subCompanies',
                'allowedResources' => [
                    'GET/api/companies',
                    'POST/api/companies',
                    '*/api/subCompanies'
                ],
                'expectedResult' => true,
            ],
            'allowed-resources-with-wildcard-at-the-end' => [
                'method' => 'GET',
                'pathInfo' => '/api/subCompanies/132449879456465/companies',
                'allowedResources' => [
                    '*/api/subCompanies*'
                ],
                'expectedResult' => true,
            ],
            'allowed-resources-for-options-method' => [
                'method' => 'OPTIONS',
                'pathInfo' => '/api/companies',
                'allowedResources' => [
                    'GET/api/companies',
                    'POST/api/companies',
                    '*/api/subCompanies'
                ],
                'expectedResult' => true,
            ],
            'allowed-resources-for-superadmin' => [
                'method' => 'GET',
                'pathInfo' => '/api/companies',
                'allowedResources' => [
                    '*'
                ],
                'expectedResult' => true,
            ],
        ];
    }
}
