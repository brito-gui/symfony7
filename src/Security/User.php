<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

final class User implements JWTUserInterface
{
    public string $email;
    public array $roles;
    public array $permissions;
    public string $profile;
    public int $companyId;
    public int $subCompanyId;

    /**
     * __construct
     *
     * @param  mixed $email
     * @param  mixed $roles
     * @param  mixed $permissions
     * @return void
     */
    public function __construct($email, array $roles, array $permissions, string $profile, int $companyId, int $subCompanyId)
    {
        $this->email = $email;
        $this->roles = $roles;
        $this->permissions = $permissions;
        $this->profile = $profile;
        $this->companyId = $companyId;
        $this->subCompanyId = $subCompanyId;
    }

    /**
     * createFromPayload
     *
     * @param  mixed $username
     * @param  mixed $payload
     * @return void
     */
    public static function createFromPayload($username, array $payload)
    {
        return new self(
            $username,  // Custom
            $payload['roles'], // Added by default
            $payload['permissions'],
            $payload['profile'],
            $payload['company_id'],
            $payload['sub_company_id'],
        );
    }

    /**
     * getRoles
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * getPermissions
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getProfile(): string
    {
        return $this->profile;
    }

    /**
     * @return int
     */
    public function getSubCompanyId(): int
    {
        return $this->subCompanyId;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {}

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}