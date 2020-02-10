<?php

namespace App\Validator\Constraints;

use App\Entity\WorkLog;
use App\Repository\WorkLogRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TimeIntervalNotOverlapsValidator extends ConstraintValidator
{
    /**
     * @var \App\Repository\WorkLogRepository
     */
    private $workLogRepository;

    public function __construct(WorkLogRepository $workLogRepository)
    {
        $this->workLogRepository = $workLogRepository;
    }

    /**
     * @param WorkLog $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($this->workLogRepository->getOverlaps($value)) {
            /** @var TimeIntervalNotOverlaps $timeIntervalConstraint */
            $timeIntervalConstraint = $constraint;
            $this->context->buildViolation($timeIntervalConstraint->message)->addViolation();
        }
    }
}
