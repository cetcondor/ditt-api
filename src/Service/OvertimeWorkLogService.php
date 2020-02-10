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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function markApproved(OvertimeWorkLog $overtimeWorkLog): void
    {
        $overtimeWorkLog->markApproved();
        $this->entityManager->flush();
    }

    public function markRejected(OvertimeWorkLog $overtimeWorkLog, string $rejectionMessage): void
    {
        $overtimeWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
