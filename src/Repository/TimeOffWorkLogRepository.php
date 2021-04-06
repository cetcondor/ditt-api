<?php

namespace App\Repository;

use App\Entity\TimeOffWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class TimeOffWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, TimeOffWorkLog::class);
    }
}
