<?php

namespace App\Validator\Constraints;

use App\Entity\WorkLog;
use App\Entity\WorkLogInterface;
use App\Repository\ContractRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class WorkLogWithinValidContractValidator extends ConstraintValidator
{
    /**
     * @var \App\Repository\ContractRepository
     */
    private $contractRepository;

    public function __construct(
        ContractRepository $contractRepository
    ) {
        $this->contractRepository = $contractRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        dump($value);
        $date = null;
        if ($value instanceof WorkLog) {
            $date = $value->getStartTime();
            dump('a');
        } else if ($value instanceof WorkLogInterface) {
            $date = $value->getDate();
            dump('b');
        } else {
            dump('c');
            return;
        }

        $contracts = $this->contractRepository->findContractsBetweenDates(
            $value->getWorkMonth()->getUser(),
            $date->setTime(0, 0, 0, 0),
            $date->setTime(23, 59, 59, 999999)
        );

        if (count($contracts) == 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
