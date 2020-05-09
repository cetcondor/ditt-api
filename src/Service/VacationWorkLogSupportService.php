<?php

namespace App\Service;

use App\Entity\VacationWorkLogSupport;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class VacationWorkLogSupportService
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
     * @param VacationWorkLogSupport[] $vacationWorkLogSupport
     */
    public function createVacationWorkLogSupport(array $vacationWorkLogSupport): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($vacationWorkLogSupport) {
            foreach ($vacationWorkLogSupport as $item) {
                if (!$item instanceof VacationWorkLogSupport) {
                    throw new \TypeError('Entity is not of type VacationWorkLogSupport.');
                }

                $em->persist($item);
            }
        });
    }
}
