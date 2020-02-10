<?php

namespace App\Repository;

use App\Entity\SupportedYear;
use App\Entity\User;
use App\Entity\Vacation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class VacationRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(Vacation::class);
        $this->repository = $repository;
    }

    public function findOne(SupportedYear $year, User $user): ?Vacation
    {
        $vacation = $this->repository->findOneBy([
            'user' => $user,
            'year' => $year,
        ]);

        if (!$vacation instanceof Vacation) {
            return null;
        }

        return $vacation;
    }
}
