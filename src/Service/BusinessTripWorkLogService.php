<?php

namespace App\Service;

use App\Entity\BusinessTripWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class BusinessTripWorkLogService
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
     * @param BusinessTripWorkLog $businessTripWorkLog
     */
    public function markApproved(BusinessTripWorkLog $businessTripWorkLog): void
    {
        $businessTripWorkLog->markApproved();
        $this->entityManager->flush();
    }

    /**
     * @param BusinessTripWorkLog $businessTripWorkLog
     * @param string $rejectionMessage
     */
    public function markRejected(BusinessTripWorkLog $businessTripWorkLog, string $rejectionMessage): void
    {
        $businessTripWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
