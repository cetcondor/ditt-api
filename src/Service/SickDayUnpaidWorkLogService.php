<?php

namespace App\Service;

use App\Entity\SickDayUnpaidWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SickDayUnpaidWorkLogService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param SickDayUnpaidWorkLog[] $sickDayUnpaidWorkLogs
     */
    public function createSickDayUnpaidWorkLogs(array $sickDayUnpaidWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($sickDayUnpaidWorkLogs) {
            foreach ($sickDayUnpaidWorkLogs as $sickDayUnpaidWorkLog) {
                if (!$sickDayUnpaidWorkLog instanceof SickDayUnpaidWorkLog) {
                    throw new \TypeError('Entity is not of type SickDayUnpaidWorkLog.');
                }

                $em->persist($sickDayUnpaidWorkLog);
            }
        });
    }
}
