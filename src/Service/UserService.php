<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Vacation;
use App\Entity\WorkMonth;
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

    public function fulfillLastApprovedWorkMonth($users): void
    {
        $currentYearMonthValue = intval(date('Y')) * 12 + intval(date('m'));

        /** @var User $iUser */
        foreach ($users as $iUser) {
            $foundWorkMonth = null;
            $foundYearMonthValue = null;

            foreach ($iUser->getWorkMonths() as $workMonth) {
                $iYearMonthValue = $workMonth->getYear()->getYear() * 12 + $workMonth->getMonth();

                if (
                    (
                        $workMonth->getStatus() === WorkMonth::STATUS_APPROVED
                        && $iYearMonthValue <= $currentYearMonthValue
                        && $iYearMonthValue == null
                    ) || (
                        $workMonth->getStatus() === WorkMonth::STATUS_APPROVED
                        && $iYearMonthValue <= $currentYearMonthValue
                        && $iYearMonthValue != null
                        && $iYearMonthValue > $foundYearMonthValue
                    )
                ) {
                    $foundWorkMonth = $workMonth;
                    $foundYearMonthValue = $foundWorkMonth->getYear()->getYear() * 12 + $foundWorkMonth->getMonth();

                    // If maximum possible was found, stop search
                    if ($foundYearMonthValue >= $currentYearMonthValue - 1) {
                        break;
                    }
                }
            }

            $iUser->setLastApprovedWorkMonth($foundWorkMonth);
        }
    }

    /**
     * @throws \Exception
     */
    public function renewApiToken(User $user): void
    {
        $user->renewApiToken();
        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function renewICalToken(User $user): void
    {
        $user->renewICalToken();
        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function resetApiToken(User $user): void
    {
        $user->setApiToken(null);
        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function resetICalToken(User $user): void
    {
        $user->setICalToken(null);
        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function setResetPasswordToken(User $user): void
    {
        $resetPasswordToken = sha1(random_bytes(32));

        $user->setResetPasswordToken($resetPasswordToken);
        $this->entityManager->flush();
    }

    public function setNewPassword(User $user, string $newPassword): void
    {
        $user->setPlainPassword($newPassword);
        $user->setResetPasswordToken(null);
        $this->entityManager->flush();
    }
}
