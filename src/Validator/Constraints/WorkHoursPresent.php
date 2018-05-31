<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class WorkHoursPresent extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Work hours are required for every month between year 2018 and 2021.';

    /**
     * @var int[]
     */
    public $supportedYears = [2018, 2019, 2020, 2021];
    /**
     * @var int[]
     */
    public $supportedMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
