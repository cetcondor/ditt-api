<?php

namespace App\Service;

use App\Entity\ParentalLeaveWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class ParentalLeaveWorkLogService
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
     * @param ParentalLeaveWorkLog[] $parentalLeaveWorkLogs
     */
    public function createParentalLeaveWorkLogs(array $parentalLeaveWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($parentalLeaveWorkLogs) {
            foreach ($parentalLeaveWorkLogs as $parentalLeaveWorkLog) {
                if (!$parentalLeaveWorkLog instanceof ParentalLeaveWorkLog) {
                    throw new \TypeError('Entity is not of type ParentalLeaveWorkLog.');
                }

                $em->persist($parentalLeaveWorkLog);
            }
        });
    }
}
