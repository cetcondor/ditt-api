<?php

namespace App\Service;

use App\Entity\OvertimeWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class OvertimeWorkLogService
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
     * @param OvertimeWorkLog $overtimeWorkLog
     */
    public function markApproved(OvertimeWorkLog $overtimeWorkLog): void
    {
        $overtimeWorkLog->markApproved();
        $this->entityManager->flush();
    }

    /**
     * @param OvertimeWorkLog $overtimeWorkLog
     * @param string $rejectionMessage
     */
    public function markRejected(OvertimeWorkLog $overtimeWorkLog, string $rejectionMessage): void
    {
        $overtimeWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
