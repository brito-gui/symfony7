<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\SubCompanyRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthController extends AbstractController
{
    #[Route('/auth/switch_sub_company', name: 'app_auth_switch_sub_company', methods:['POST'])]
    public function switchSubCompany(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, Request $request, SubCompanyRepository $repository): Response
    {
        $payload = json_decode($request->getContent());

        if (empty($payload->sub_company->uuid)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid payload. You must provide a valid subCompany");
        }

        $subCompany = $repository->findOneBy(['uuid' => $payload->sub_company->uuid]);

        if (is_null($subCompany)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "subCompany not found");
        }

        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());

        /**
         * @var User
         */
        $user = $tokenStorageInterface->getToken()->getUser();
        $decodedJwtToken['sub_company_uuid'] = $subCompany->getUuid() ?? null;
        $decodedJwtToken['sub_company_name'] = $subCompany->getName() ?? null;
        $decodedJwtToken['permissions'] = $user->getUserRoleBySubCompany($subCompany) ? $user->getUserRoleBySubCompany($subCompany)->getRole()->getPermissions() : null;        

        if (empty($decodedJwtToken['permissions'])) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Given subCompany doesn't have relations with given user");
        }

        $token = $jwtManager->createFromPayload($user, $decodedJwtToken);

        return new JsonResponse(['token' => $token]);
    }
}
