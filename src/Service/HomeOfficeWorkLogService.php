<?php

namespace App\Service;

use App\Entity\HomeOfficeWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class HomeOfficeWorkLogService
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
     * @param HomeOfficeWorkLog $homeOfficeWorkLog
     */
    public function markApproved(HomeOfficeWorkLog $homeOfficeWorkLog): void
    {
        $homeOfficeWorkLog->markApproved();
        $this->entityManager->flush();
    }

    /**
     * @param HomeOfficeWorkLog $homeOfficeWorkLog
     * @param string $rejectionMessage
     */
    public function markRejected(HomeOfficeWorkLog $homeOfficeWorkLog, string $rejectionMessage): void
    {
        $homeOfficeWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
