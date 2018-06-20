<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class HomeOfficeWorkLogRepository
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
        $repository = $entityManager->getRepository(HomeOfficeWorkLogRepository::class);
        $this->repository = $repository;
    }
}
