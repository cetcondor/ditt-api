<?php

namespace App\Repository;

use App\Entity\BusinessTripWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class BusinessTripWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, BusinessTripWorkLog::class);
    }
}
