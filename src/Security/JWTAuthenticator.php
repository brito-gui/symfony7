<?php

namespace App\Security;

use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator as LexikJWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\CssSelector\XPath\TranslatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JWTAuthenticator extends LexikJWTAuthenticator
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager, EventDispatcherInterface $eventDispatcher, TokenExtractorInterface $tokenExtractor, UserProviderInterface $userProvider, TranslatorInterface $translator = null)
    {
        parent::__construct($jwtManager, $eventDispatcher, $tokenExtractor, $userProvider, $translator);

        $this->jwtManager = $jwtManager;
    }

    /**
     * doAuthenticate
     *
     * @param  Request $request
     *
     * @return Passport
     */
    public function doAuthenticate(Request $request): Passport
    {
        $token = $this->getTokenExtractor()->extract($request);
        if ($token === false) {
            throw new \LogicException('Unable to extract a JWT token from the request. Also, make sure to call `supports()` before `authenticate()` to get a proper client error.');
        }

        try {
            if (!$payload = $this->jwtManager->parse($token)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new ExpiredTokenException();
            }

            throw new InvalidTokenException('Invalid JWT Token', 0, $e);
        }

        // Check allowed resources from user JWT payload
        if (!$this->checkPermissions($request->getMethod(), $request->getPathInfo(), $payload['permissions'])) {
            throw new AccessDeniedException();
        }

        $idClaim = $this->jwtManager->getUserIdClaim();
        if (!isset($payload[$idClaim])) {
            throw new InvalidPayloadException($idClaim);
        }

        $passport = new SelfValidatingPassport(
            new UserBadge(
                (string)$payload[$idClaim],
                function ($userIdentifier) use ($payload) {
                    return $this->loadUser($payload, $userIdentifier);
                }
            )
        );

        $passport->setAttribute('payload', $payload);
        $passport->setAttribute('token', $token);

        return $passport;
    }

    /**
     * @param  string $method
     * @param  string $pathInfo
     * @param  array  $allowedResources
     *
     * @return bool
     */
    public function checkPermissions(string $method, string $pathInfo, array $allowedResources): bool
    {
        if (in_array('*', $allowedResources, true)) {
            return true;
        }

        foreach ($allowedResources as $resource) {

            // convert resource to a regular exprssion format, replacing * for a wildcard
            $resourceRegex = str_replace("\*", ".*", preg_quote($resource, '/'));

            // check if the path match with method + pathInfo
            if (preg_match(sprintf("/^%s$/", $resourceRegex), $method . $pathInfo) || $method === 'OPTIONS') {
                return true;
            }
        }

        return false;
    }
}
