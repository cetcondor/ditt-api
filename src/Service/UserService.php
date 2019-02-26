<?php

namespace App\Service;

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
     * @var ConfigService
     */
    private $configService;

    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ConfigService $configService
     * @param VacationWorkLogRepository $vacationWorkLogRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigService $configService,
        VacationWorkLogRepository $vacationWorkLogRepository
    ) {
        $this->entityManager = $entityManager;
        $this->configService = $configService;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
    }

    /**
     * @param User $user
     * @return array
     */
    public function calculateRemainingVacationDaysByYear(User $user): array
    {
        $config = $this->configService->getConfig();
        $remainingVacationDaysByYear = [];

        foreach ($config->getSupportedYears() as $supportedYear) {
            $remainingVacationDaysByYear[$supportedYear->getYear()] = $this->vacationWorkLogRepository->getRemainingVacationDays(
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
    public function renewApiToken(User $user): void
    {
        $user->renewApiToken();
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    public function resetApiToken(User $user): void
    {
        $user->setApiToken(null);
        $this->entityManager->flush();
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
