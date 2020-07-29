<?php

namespace App\Service;

use App\Entity\TimeOffWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class TimeOffWorkLogService
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
     * @param TimeOffWorkLog[] $timeOffWorkLogs
     */
    public function createTimeOffWorkLogs(array $timeOffWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($timeOffWorkLogs) {
            foreach ($timeOffWorkLogs as $timeOffWorkLog) {
                if (!$timeOffWorkLog instanceof TimeOffWorkLog) {
                    throw new \TypeError('Entity is not of type TimeOffWorkLog.');
                }

                $em->persist($timeOffWorkLog);
            }
        });
    }

    public function markApproved(TimeOffWorkLog $timeOffWorkLog): void
    {
        $timeOffWorkLog->markApproved();
        $this->entityManager->flush();
    }

    public function markRejected(TimeOffWorkLog $timeOffWorkLog, string $rejectionMessage): void
    {
        $timeOffWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
