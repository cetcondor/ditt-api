<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class VacationDaysNotExhausted extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Set duration exceeds number of vacation days allocated for this year';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
