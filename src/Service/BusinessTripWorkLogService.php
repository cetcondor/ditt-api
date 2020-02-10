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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function markApproved(BusinessTripWorkLog $businessTripWorkLog): void
    {
        $businessTripWorkLog->markApproved();
        $this->entityManager->flush();
    }

    public function markRejected(BusinessTripWorkLog $businessTripWorkLog, string $rejectionMessage): void
    {
        $businessTripWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
