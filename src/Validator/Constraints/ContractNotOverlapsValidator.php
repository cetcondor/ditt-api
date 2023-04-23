<?php

namespace App\Validator\Constraints;

use App\Entity\Contract;
use App\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContractNotOverlapsValidator extends ConstraintValidator
{
    /**
     * @param User $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$this->validateContracts($value->getContracts())) {
            /** @var ContractNotOverlaps $contractConstraint */
            $contractConstraint = $constraint;
            $this->context->buildViolation($contractConstraint->message)->addViolation();
        }
    }

    /**
     * Accepts array of Contract objects and returns true if following validation conditions are met:
     * - There is at maximum one contract without end date
     * - No two contracts overlap (null end date time is considered as infinity)
     *
     * @param Contract[] $contracts
     */
    private function validateContracts(array $contracts): bool
    {
        if (count($contracts) < 2) {
            return true;
        }

        $contractsWithoutEndDate = 0;

        foreach ($contracts as $contract) {
            if ($contract->getEndDateTime() === null) {
                ++$contractsWithoutEndDate;
            }
        }

        if ($contractsWithoutEndDate > 1) {
            return false;
        }

        foreach ($contracts as $contract) {
            foreach ($contracts as $otherContract) {
                if ($contract === $otherContract) {
                    continue;
                }

                if ($contract->getEndDateTime() === null) {
                    if ($otherContract->getEndDateTime() === null) {
                        return false;
                    }
                    if ($otherContract->getEndDateTime() > $contract->getStartDateTime()) {
                        return false;
                    }
                } else {
                    if ($otherContract->getEndDateTime() === null) {
                        if ($contract->getEndDateTime() > $otherContract->getStartDateTime()) {
                            return false;
                        }
                    } else {
                        if ($contract->getEndDateTime() > $otherContract->getStartDateTime() && $otherContract->getEndDateTime() > $contract->getStartDateTime()) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}
