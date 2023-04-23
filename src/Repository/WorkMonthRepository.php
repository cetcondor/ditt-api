<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkLogInterface;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;

class WorkMonthRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(WorkMonth::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function findById(int $id): ?WorkMonth
    {
        return $this->repository->findOneBy(['id' => $id]);
    }

    /**
     * @return WorkMonth[]
     */
    public function findAllOpenedByUserBetweenDates(User $user, \DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('wm')
            ->select('wm')
            ->where('wm.user = :user')
            ->andWhere('wm.status != :status')
            ->setParameter('user', $user)
            ->setParameter('status', WorkMonth::STATUS_OPENED);

        $expr = $queryBuilder->expr();
        $yearFrom = $dateFrom->format('Y');
        $monthFrom = $dateFrom->format('m');

        if ($dateTo === null) {
            $queryBuilder->andWhere(
                $expr->orX(
                    $expr->gt('wm.year', $yearFrom),
                    $expr->andX(
                        $expr->eq('wm.year', $yearFrom),
                        $expr->gte('wm.month', $monthFrom)
                    )
                )
            );
        } else {
            $yearTo = $dateTo->format('Y');
            $monthTo = $dateTo->format('m');
            $queryBuilder->andWhere(
                $expr->orX(
                    $expr->gt('wm.year', $yearFrom),
                    $expr->andX(
                        $expr->eq('wm.year', $yearFrom),
                        $expr->gte('wm.month', $monthFrom)
                    )
                ),
                $expr->orX(
                    $expr->lt('wm.year', $yearTo),
                    $expr->andX(
                        $expr->eq('wm.year', $yearTo),
                        $expr->lte('wm.month', $monthTo)
                    )
                )
            );
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllApproved(): array
    {
        return $this->repository->createQueryBuilder('wm')
            ->select('wm')
            ->where('wm.status = :status')
            ->setParameter('status', 'APPROVED')
            ->getQuery()
            ->getResult();
    }

    public function findByWorkLogAndUser(WorkLogInterface $workLog, User $user): ?WorkMonth
    {
        try {
            return $this->repository->createQueryBuilder('wm')
                ->select('wm')
                ->where('wm.month = :workLogMonth')
                ->andWhere('wm.year = :workLogYear')
                ->andWhere('wm.user = :user')
                ->setParameter('workLogMonth', $workLog->resolveWorkLogMonth())
                ->setParameter('workLogYear', $workLog->resolveWorkLogYear())
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleResult();
        } catch (UnexpectedResultException $e) {
            return null;
        }
    }
}
