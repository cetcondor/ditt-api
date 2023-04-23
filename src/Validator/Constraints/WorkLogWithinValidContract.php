<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class WorkLogWithinValidContract extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Work log is not within a valid contract.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
