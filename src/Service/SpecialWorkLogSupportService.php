<?php

namespace App\Service;

use App\Entity\SpecialWorkLogSupportInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SpecialWorkLogSupportService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param SpecialWorkLogSupportInterface[] $specialWorkLogSupport
     */
    public function createWorkLogSupport(array $specialWorkLogSupport): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($specialWorkLogSupport) {
            foreach ($specialWorkLogSupport as $item) {
                if (!$item instanceof SpecialWorkLogSupportInterface) {
                    throw new \TypeError('Entity is not of type SpecialWorkLogSupportInterface.');
                }

                $em->persist($item);
            }
        });
    }
}
