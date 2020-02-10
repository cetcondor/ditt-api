<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class VacationPresent extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Vacations are required for each supported year.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
