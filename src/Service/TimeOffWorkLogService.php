<?php

namespace App\Service;

use App\Entity\TimeOffWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class TimeOffWorkLogService
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
     * @param TimeOffWorkLog $timeOffWorkLog
     */
    public function markApproved(TimeOffWorkLog $timeOffWorkLog): void
    {
        $timeOffWorkLog->markApproved();
        $this->entityManager->flush();
    }

    /**
     * @param TimeOffWorkLog $timeOffWorkLog
     * @param string $rejectionMessage
     */
    public function markRejected(TimeOffWorkLog $timeOffWorkLog, string $rejectionMessage): void
    {
        $timeOffWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
