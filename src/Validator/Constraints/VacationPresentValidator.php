<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\WorkLogRepository;
use App\Service\ConfigService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VacationPresentValidator extends ConstraintValidator
{
    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var \App\Repository\WorkLogRepository
     */
    private $workLogRepository;

    /**
     * @param ConfigService $configService
     * @param WorkLogRepository $workLogRepository
     */
    public function __construct(
        ConfigService $configService,
        WorkLogRepository $workLogRepository
    ) {
        $this->configService = $configService;
        $this->workLogRepository = $workLogRepository;
    }

    /**
     * @param User $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $config = $this->configService->getConfig();

        if (!$value instanceof User || !$constraint instanceof VacationPresent) {
            return;
        }

        $present = [];

        foreach ($value->getVacations() as $vacation) {
            if (!in_array($vacation->getYear()->getYear(), $present)) {
                $present[] = $vacation->getYear()->getYear();
            }
        }

        if (count($present) !== count($config->getSupportedYears())) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }

        foreach ($config->getSupportedYears() as $supportedYear) {
            if (!in_array($supportedYear->getYear(), $present)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
