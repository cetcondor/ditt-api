<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class VacationDaysNotExhausted extends Constraint
{
    public $message = 'Set duration exceeds number of vacation days allocated for this year';

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
