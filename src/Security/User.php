<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

final class User implements JWTUserInterface
{
    public string $email;
    public array $roles;
    public array $permissions;

    /**
     * __construct
     *
     * @param  mixed $email
     * @param  mixed $roles
     * @param  mixed $permissions
     * @return void
     */
    public function __construct($email, array $roles, array $permissions)
    {
        $this->email = $email;
        $this->roles = $roles;
        $this->permissions = $permissions;
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