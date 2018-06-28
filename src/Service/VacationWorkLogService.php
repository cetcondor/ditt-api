<?php

namespace App\Service;

use App\Entity\VacationWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class VacationWorkLogService
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
     * @param VacationWorkLog $vacationWorkLog
     */
    public function markApproved(VacationWorkLog $vacationWorkLog): void
    {
        $vacationWorkLog->markApproved();
        $this->entityManager->flush();
    }

    /**
     * @param VacationWorkLog $vacationWorkLog
     * @param string $rejectionMessage
     */
    public function markRejected(VacationWorkLog $vacationWorkLog, string $rejectionMessage): void
    {
        $vacationWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
