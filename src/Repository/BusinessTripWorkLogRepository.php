<?php

namespace App\Repository;

use App\Entity\BusinessTripWorkLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class BusinessTripWorkLogRepository
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
        $repository = $entityManager->getRepository(BusinessTripWorkLog::class);
        $this->repository = $repository;
    }
}
