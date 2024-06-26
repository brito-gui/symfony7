<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /**
         * @var User
         */

        $user    = $event->getUser();
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();
        $payload['ip'] = $request->getClientIp();
        $payload['profile'] = $user->getProfile();
        $payload['company_uuid'] = !is_null($user->getCompany()) ? $user->getCompany()->getUuid() : null;
        $payload['company_id'] = !is_null($user->getCompany()) ? $user->getCompany()->getId() : 0;
        $payload['company_name'] = !is_null($user->getCompany()) ? $user->getCompany()->getName() : null;
        $payload['sub_company_uuid'] = $payload['sub_company_uuid'] ?? (!is_null($user->getDefaultSubCompany()) ? $user->getDefaultSubCompany()->getUuid() : null);
        $payload['sub_company_id'] = $payload['sub_company_id'] ?? (!is_null($user->getDefaultSubCompany()) ? $user->getDefaultSubCompany()->getId() : 0);
        $payload['sub_company_name'] = $payload['sub_company_name'] ?? (!is_null($user->getDefaultSubCompany()) ? $user->getDefaultSubCompany()->getName() : null);
        $payload['permissions'] = $payload['permissions'] ?? $this->getPermissions($user);
        $event->setData($payload);

        $header = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }

    /**
     * getPermissions
     *
     * @param  mixed $user
     *
     * @return array
     */
    private function getPermissions(User $user): array
    {
        if ($user->getProfile() === User::PROFILE_USER) {
            return $user->getUserRoleBySubCompany($user->getDefaultSubCompany()) ? $user->getUserRoleBySubCompany($user->getDefaultSubCompany())->getRole()->getPermissions() : [];
        }
        return $user->getUserRoles()->first()->getRole()->getPermissions();
    }
}
