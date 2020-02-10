<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class WorkHoursPresent extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Work hours are required for every month of each supported year.';

    /**
     * @var int[]
     */
    public $supportedMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
