<?php

namespace App\Service;

use App\Entity\BusinessTripWorkLog;
use Doctrine\ORM\EntityManager;
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

    /**
     * @param BusinessTripWorkLog[] $businessTripWorkLogs
     */
    public function createBusinessTripWorkLogs(array $businessTripWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($businessTripWorkLogs) {
            foreach ($businessTripWorkLogs as $businessTripWorkLog) {
                if (!$businessTripWorkLog instanceof BusinessTripWorkLog) {
                    throw new \TypeError('Entity is not of type BusinessTripWorkLog.');
                }

                $em->persist($businessTripWorkLog);
            }
        });
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
