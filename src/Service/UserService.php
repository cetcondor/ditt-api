<?php

namespace App\Service;

use App\Entity\Config;
use App\Entity\User;
use App\Repository\VacationWorkLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param VacationWorkLogRepository $vacationWorkLogRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        VacationWorkLogRepository $vacationWorkLogRepository
    ) {
        $this->entityManager = $entityManager;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
    }

    /**
     * @param User $user
     * @return array
     */
    public function calculateRemainingVacationDaysByYear(User $user): array
    {
        $remainingVacationDaysByYear = [];

        foreach ((new Config())->getSupportedYear() as $supportedYear) {
            $remainingVacationDaysByYear[$supportedYear] = $this->vacationWorkLogRepository->getRemainingVacationDays(
                $user,
                $supportedYear
            );
        }

        return $remainingVacationDaysByYear;
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
