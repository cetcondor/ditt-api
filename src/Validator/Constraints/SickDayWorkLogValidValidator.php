<?php

namespace App\Validator\Constraints;

use App\Entity\SickDayWorkLog;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SickDayWorkLogValidValidator extends ConstraintValidator
{
    /**
     * @param SickDayWorkLog $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof SickDayWorkLog || !$constraint instanceof SickDayWorkLogValid) {
            return;
        }

        if (
            SickDayWorkLog::VARIANT_SICK_CHILD === $value->getVariant()
            && (empty($value->getChildName()) || empty($value->getChildDateOfBirth()))
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
