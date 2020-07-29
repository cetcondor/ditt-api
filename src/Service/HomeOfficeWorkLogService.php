<?php

namespace App\Service;

use App\Entity\HomeOfficeWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class HomeOfficeWorkLogService
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
     * @param HomeOfficeWorkLog[] $homeOfficeWorkLogs
     */
    public function createHomeOfficeWorkLogs(array $homeOfficeWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($homeOfficeWorkLogs) {
            foreach ($homeOfficeWorkLogs as $homeOfficeWorkLog) {
                if (!$homeOfficeWorkLog instanceof HomeOfficeWorkLog) {
                    throw new \TypeError('Entity is not of type HomeOfficeWorkLog.');
                }

                $em->persist($homeOfficeWorkLog);
            }
        });
    }

    public function markApproved(HomeOfficeWorkLog $homeOfficeWorkLog): void
    {
        $homeOfficeWorkLog->markApproved();
        $this->entityManager->flush();
    }

    public function markRejected(HomeOfficeWorkLog $homeOfficeWorkLog, string $rejectionMessage): void
    {
        $homeOfficeWorkLog->markRejected($rejectionMessage);
        $this->entityManager->flush();
    }
}
