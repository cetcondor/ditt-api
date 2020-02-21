<?php

namespace App\Service;

use App\Entity\BanWorkLog;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class BanWorkLogService
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
     * @param BanWorkLog[] $banWorkLogs
     */
    public function createBanWorkLogs(array $banWorkLogs): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($banWorkLogs) {
            foreach ($banWorkLogs as $banWorkLog) {
                if (!$banWorkLog instanceof BanWorkLog) {
                    throw new \TypeError('Entity is not of type BanWorkLog.');
                }

                $em->persist($banWorkLog);
            }
        });
    }
}
