<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Vacation;
use App\Repository\VacationWorkLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function fullfilRemainingVacationDays(User &$user): void
    {
        $config = $this->configService->getConfig();
        /** @var Vacation[] $vacations */
        $vacations = [];

        foreach ($user->getVacations() as $vacation) {
            foreach ($config->getSupportedYears() as $supportedYear) {
                if ($vacation->getYear()->getYear() === $supportedYear->getYear()) {
                    $vacations[] = $vacation->setRemainingVacationDays(
                        $this->vacationWorkLogRepository->getRemainingVacationDays(
                            $user,
                            $supportedYear
                        )
                    );

                    break;
                }
            }
        }

        $user->setVacations(new ArrayCollection($vacations));
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
