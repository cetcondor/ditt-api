<?php

namespace App\Repository;

use App\Entity\SupportedYear;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class VacationWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, VacationWorkLog::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getRemainingVacationDays(User $user, SupportedYear $year): int
    {
        $vacationWorkLogsCount = (int) $this->getRepository()->createQueryBuilder('vwl')
            ->select('COUNT(vwl.id)')
            ->leftJoin('vwl.workMonth', 'wm')
            ->where('wm.user = :user')
            ->andWhere('wm.year = :year')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->getQuery()
            ->getSingleScalarResult();

        $vacationDays = 0;
        $vacationDaysCorrection = 0;

        foreach ($user->getVacations() as $vacation) {
            if ($vacation->getYear()->getYear() === $year->getYear()) {
                $vacationDays = $vacation->getVacationDays();
                $vacationDaysCorrection = $vacation->getVacationDaysCorrection();

                break;
            }
        }

        return ($vacationDays + $vacationDaysCorrection) - $vacationWorkLogsCount;
    }
}
