<?php

namespace App\Repository;

use App\Entity\TimeOffWorkLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class TimeOffWorkLogRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(TimeOffWorkLog::class);
        $this->repository = $repository;
    }
}
