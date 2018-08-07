<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class SickDayWorkLogValid extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Sick day work log required child`s name and date of birth if sick child is selected.';

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
