<?php

namespace App\Repository;

use App\Entity\SpecialLeaveWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class SpecialLeaveWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, SpecialLeaveWorkLog::class);
    }
}
