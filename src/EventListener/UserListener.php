<?php

namespace App\EventListener;

use App\Entity\User;
use App\Services\Encryptor\Encryptor;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * UserListener
 */
#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 499, connection: 'default')]
class UserListener
{
    /**
     * @var UserPasswordHasherInterface
     */
    protected $passwordHasher;

    /**
     * __construct
     *
     * @param Encryptor $encryptor
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * preUpdate
     *
     * @param PreUpdateEventArgs $event
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $object = $event->getObject();

        if (!is_a($object, User::class)) {
            return;
        }

        $this->hashPassword($object);
    }

    /**
     * preUpdate
     *
     * @param PrePersistEventArgs $event
     *
     * @return void
     */
    public function prePersist(PrePersistEventArgs $event): void
    {
        $object = $event->getObject();

        if (!is_a($object, User::class)) {
            return;
        }

        $this->hashPassword($object);
    }

    private function hashPassword(User $user)
    {
        if(!$user->getPlainPassword()) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
        $user->eraseCredentials();
    }
}
