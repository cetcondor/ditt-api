<?php

namespace App\Validator\Constraints;

use App\Entity\VacationWorkLog;
use App\Repository\VacationWorkLogRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VacationDaysNotExhaustedValidator extends ConstraintValidator
{
    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    public function __construct(VacationWorkLogRepository $vacationWorkLogRepository)
    {
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
    }

    /**
     * @param VacationWorkLog $value
     */
    public function validate($value, Constraint $constraint): void
    {
        $user = $value->getWorkMonth()->getUser();
        $year = $value->getWorkMonth()->getYear();

        if ($this->vacationWorkLogRepository->getRemainingVacationDays($user, $year) < 1) {
            /** @var VacationDaysNotExhausted $vacationDasNotExhaustedConstraint */
            $vacationDasNotExhaustedConstraint = $constraint;
            $this->context->buildViolation($vacationDasNotExhaustedConstraint->message)->addViolation();
        }
    }
}
