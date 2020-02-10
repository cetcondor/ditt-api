<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class TimeIntervalNotOverlaps extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Time interval overlaps another existing time interval.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
