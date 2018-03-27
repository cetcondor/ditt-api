<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class TimeIntervalNotOverlaps extends Constraint
{
    public $message = 'Time interval overlaps another existing time interval.';

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
