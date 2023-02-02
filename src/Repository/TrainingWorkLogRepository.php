<?php

namespace App\Repository;

use App\Entity\TrainingWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class TrainingWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, TrainingWorkLog::class);
    }
}
