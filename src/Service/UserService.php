<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    public function setResetPasswordToken(User $user): void
    {
        $resetPasswordToken = sha1(random_bytes(32));

        $user->setResetPasswordToken($resetPasswordToken);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param string $newPassword
     */
    public function setNewPassword(User $user, string $newPassword): void
    {
        $user->setPlainPassword($newPassword);
        $user->setResetPasswordToken(null);
        $this->entityManager->flush();
    }
}
