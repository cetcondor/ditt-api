<?php

namespace App\Validator\Constraints;

use App\Entity\Config;
use App\Entity\User;
use App\Repository\WorkLogRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class WorkHoursPresentValidator extends ConstraintValidator
{
    /**
     * @var \App\Repository\WorkLogRepository
     */
    private $workLogRepository;

    /**
     * @param WorkLogRepository $workLogRepository
     */
    public function __construct(WorkLogRepository $workLogRepository)
    {
        $this->workLogRepository = $workLogRepository;
    }

    /**
     * @param User $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $config = new Config();

        if (!$value instanceof User || !$constraint instanceof WorkHoursPresent) {
            return;
        }

        $present = [];

        foreach ($value->getWorkHours() as $workHours) {
            if (!isset($present[$workHours->getYear()])) {
                $present[$workHours->getYear()] = [];
            }

            $present[$workHours->getYear()][] = $workHours->getMonth();
        }

        if (count($present) !== count($config->getSupportedYear())) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }

        foreach ($config->getSupportedYear() as $supportedYear) {
            if (isset($present[$supportedYear])) {
                $uniqueMonths = array_unique($present[$supportedYear]);

                if (count($uniqueMonths) !== count($constraint->supportedMonths)) {
                    $this->context->buildViolation($constraint->message)->addViolation();

                    return;
                }

                foreach ($uniqueMonths as $uniqueMonth) {
                    if (!in_array($uniqueMonth, $constraint->supportedMonths)) {
                        $this->context->buildViolation($constraint->message)->addViolation();

                        return;
                    }
                }
            } else {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
