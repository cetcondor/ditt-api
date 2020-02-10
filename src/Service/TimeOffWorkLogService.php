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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
