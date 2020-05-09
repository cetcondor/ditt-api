<?php

namespace App\Service;

use App\Entity\SpecialLeaveWorkLogSupport;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class SpecialLeaveWorkLogSupportService
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
     * @param SpecialLeaveWorkLogSupport[] $specialLeaveWorkLogSupport
     */
    public function createSpecialLeaveWorkLogSupport(array $specialLeaveWorkLogSupport): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($specialLeaveWorkLogSupport) {
            foreach ($specialLeaveWorkLogSupport as $item) {
                if (!$item instanceof SpecialLeaveWorkLogSupport) {
                    throw new \TypeError('Entity is not of type SpecialLeaveWorkLogSupport.');
                }

                $em->persist($item);
            }
        });
    }
}
