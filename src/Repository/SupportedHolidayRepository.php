<?php

namespace App\Repository;

use App\Entity\SupportedHoliday;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SupportedHolidayRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(SupportedHoliday::class);

        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return SupportedHoliday[]
     */
    public function findAll(): array
    {
        return $this->repository->createQueryBuilder('sh')
            ->select('sh')
            ->orderBy('sh.month', 'ASC')
            ->addOrderBy('sh.day', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
