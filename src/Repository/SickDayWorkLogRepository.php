<?php

namespace App\Repository;

use App\Entity\SickDayWorkLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SickDayWorkLogRepository
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
        $repository = $entityManager->getRepository(SickDayWorkLog::class);

        $this->repository = $repository;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }
}
