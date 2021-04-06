<?php

namespace App\Service;

use App\Entity\SpecialWorkLogInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SpecialWorkLogService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param SpecialWorkLogInterface[] $specialWorkLogs
     */
    public function createWorkLogs(array $specialWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($specialWorkLogs) {
            foreach ($specialWorkLogs as $specialWorkLog) {
                if (!$specialWorkLog instanceof SpecialWorkLogInterface) {
                    throw new \TypeError('Entity is not of type SpecialWorkLogInterface.');
                }

                $em->persist($specialWorkLog);
            }
        });
    }

    public function markApproved(SpecialWorkLogInterface $specialWorkLog): void
    {
        $specialWorkLog->markApproved();
        $this->entityManager->flush();
    }

    public function markRejected(SpecialWorkLogInterface $specialWorkLog, string $rejectionMessage): void
    {
        $specialWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
