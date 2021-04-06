<?php

namespace App\Repository;

use App\Entity\OvertimeWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class OvertimeWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, OvertimeWorkLog::class);
    }
}
