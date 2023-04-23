<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContractNotOverlaps extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Contract overlaps another existing contract.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
