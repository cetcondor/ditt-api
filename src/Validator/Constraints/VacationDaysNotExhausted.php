<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class VacationDaysNotExhausted extends Constraint
{
    public $message = 'Vacation days for given year have been already exhausted.';

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
