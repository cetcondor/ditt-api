<?php

namespace App\Service;

use App\Entity\SickDayWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SickDayWorkLogService
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
     * @param SickDayWorkLog[] $sickDayWorkLogs
     */
    public function createSickDayWorkLogs(array $sickDayWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($sickDayWorkLogs) {
            foreach ($sickDayWorkLogs as $sickDayWorkLog) {
                if (!$sickDayWorkLog instanceof SickDayWorkLog) {
                    throw new \TypeError('Entity is not of type SickDayWorkLog.');
                }

                $em->persist($sickDayWorkLog);
            }
        });
    }
}
