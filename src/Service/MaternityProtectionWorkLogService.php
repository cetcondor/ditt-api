<?php

namespace App\Service;

use App\Entity\MaternityProtectionWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class MaternityProtectionWorkLogService
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
     * @param MaternityProtectionWorkLog[] $MaternityProtectionWorkLogs
     */
    public function createMaternityProtectionWorkLogs(array $MaternityProtectionWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($MaternityProtectionWorkLogs) {
            foreach ($MaternityProtectionWorkLogs as $MaternityProtectionWorkLog) {
                if (!$MaternityProtectionWorkLog instanceof MaternityProtectionWorkLog) {
                    throw new \TypeError('Entity is not of type MaternityProtectionWorkLog.');
                }

                $em->persist($MaternityProtectionWorkLog);
            }
        });
    }
}
