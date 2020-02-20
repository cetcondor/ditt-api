<?php

namespace App\Service;

use App\Entity\SpecialLeaveWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SpecialLeaveWorkLogService
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
     * @param SpecialLeaveWorkLog[] $specialLeaveWorkLogs
     */
    public function createSpecialLeaveWorkLogs(array $specialLeaveWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($specialLeaveWorkLogs) {
            foreach ($specialLeaveWorkLogs as $specialLeaveWorkLog) {
                if (!$specialLeaveWorkLog instanceof SpecialLeaveWorkLog) {
                    throw new \TypeError('Entity is not of type SpecialLeaveWorkLog.');
                }

                $em->persist($specialLeaveWorkLog);
            }
        });
    }

    public function markApproved(SpecialLeaveWorkLog $specialLeaveWorkLog): void
    {
        $specialLeaveWorkLog->markApproved();
        $this->entityManager->flush();
    }

    public function markRejected(SpecialLeaveWorkLog $specialLeaveWorkLog, string $rejectionMessage): void
    {
        $specialLeaveWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
