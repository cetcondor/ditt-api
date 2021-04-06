<?php

namespace App\Repository;

use App\Entity\HomeOfficeWorkLog;
use Doctrine\ORM\EntityManagerInterface;

class HomeOfficeWorkLogRepository extends SpecialWorkLogRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, HomeOfficeWorkLog::class);
    }
}
