<?php

namespace App\Repository;

use App\Entity\SupportedYear;
use App\Entity\User;
use App\Entity\WorkHours;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class WorkHoursRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(WorkHours::class);
        $this->repository = $repository;
    }

    public function findOne(SupportedYear $year, int $month, User $user): ?WorkHours
    {
        $workHours = $this->repository->findOneBy([
            'month' => $month,
            'user' => $user,
            'year' => $year,
        ]);

        if (!$workHours instanceof WorkHours) {
            return null;
        }

        return $workHours;
    }
}
