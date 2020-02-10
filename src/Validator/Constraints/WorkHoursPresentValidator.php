<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\WorkLogRepository;
use App\Service\ConfigService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class WorkHoursPresentValidator extends ConstraintValidator
{
    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var \App\Repository\WorkLogRepository
     */
    private $workLogRepository;

    public function __construct(
        ConfigService $configService,
        WorkLogRepository $workLogRepository
    ) {
        $this->configService = $configService;
        $this->workLogRepository = $workLogRepository;
    }

    /**
     * @param User $value
     */
    public function validate($value, Constraint $constraint): void
    {
        $config = $this->configService->getConfig();

        if (!$value instanceof User || !$constraint instanceof WorkHoursPresent) {
            return;
        }

        $present = [];

        foreach ($value->getWorkHours() as $workHours) {
            if (!isset($present[$workHours->getYear()->getYear()])) {
                $present[$workHours->getYear()->getYear()] = [];
            }

            $present[$workHours->getYear()->getYear()][] = $workHours->getMonth();
        }

        if (count($present) !== count($config->getSupportedYears())) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }

        foreach ($config->getSupportedYears() as $supportedYear) {
            if (isset($present[$supportedYear->getYear()])) {
                $uniqueMonths = array_unique($present[$supportedYear->getYear()]);

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
