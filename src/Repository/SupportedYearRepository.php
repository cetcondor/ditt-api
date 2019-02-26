<?php

namespace App\Repository;

use App\Entity\SupportedYear;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SupportedYearRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(SupportedYear::class);

        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return SupportedYear[]
     */
    public function findAll(): array
    {
        return $this->repository->createQueryBuilder('sy')
            ->select('sy')
            ->orderBy('sy.year', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
