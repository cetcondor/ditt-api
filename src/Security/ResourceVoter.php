<?php

namespace App\Security;

use App\Entity\Contract;
use App\Entity\SpecialWorkLogSupportInterface;
use App\Entity\SupervisorWorkLogInterface;
use App\Entity\User;
use App\Entity\Vacation;
use App\Entity\WorkLogInterface;
use App\Entity\WorkMonth;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResourceVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * ResourceVoter constructor.
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if ($subject instanceof Contract) {
            return true;
        }

        if ($subject instanceof SupervisorWorkLogInterface) {
            return true;
        }

        if ($subject instanceof User) {
            return true;
        }

        if ($subject instanceof Vacation) {
            return true;
        }

        if ($subject instanceof WorkLogInterface) {
            return true;
        }

        if ($subject instanceof SpecialWorkLogSupportInterface) {
            return true;
        }

        if ($subject instanceof WorkMonth) {
            return true;
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @throws \LogicException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::VIEW) {
            return $this->canView($subject, $user, $token);
        } elseif ($attribute === self::EDIT) {
            return $this->canEdit($subject, $user, $token);
        }

        throw new \LogicException(sprintf('Invalid attribute for voter. Allowed are %s and %s', self::VIEW, self::EDIT));
    }

    // VIEW

    /**
     * @param mixed $subject
     */
    private function canView($subject, User $user, TokenInterface $token): bool
    {
        if ($subject instanceof Contract) {
            return $this->canViewContract($subject, $user, $token);
        }

        if ($subject instanceof User) {
            return $this->canViewUser($subject, $user, $token);
        }

        if ($subject instanceof Vacation) {
            return $this->canViewVacation($subject, $user, $token);
        }

        if ($subject instanceof WorkLogInterface) {
            return $this->canViewWorkLog($subject, $user, $token);
        }

        if ($subject instanceof SpecialWorkLogSupportInterface) {
            return $this->canViewWorkLogSupport($subject, $user, $token);
        }

        if ($subject instanceof WorkMonth) {
            return $this->canViewWorkMonth($subject, $user, $token);
        }

        return false;
    }

    private function canViewContract(Contract $contract, User $user, TokenInterface $token): bool
    {
        return $contract->getUser() === $user
            || in_array($user, $contract->getUser()->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    private function canViewUser(User $subject, User $user, TokenInterface $token): bool
    {
        return $subject === $user
            || in_array($user, $subject->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    private function canViewVacation(Vacation $vacation, User $user, TokenInterface $token): bool
    {
        return $vacation->getUser() === $user
            || in_array($user, $vacation->getUser()->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    private function canViewWorkLog(WorkLogInterface $workLog, User $user, TokenInterface $token): bool
    {
        return $workLog->getWorkMonth()->getUser() === $user
            || in_array($user, $workLog->getWorkMonth()->getUser()->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
    }

    private function canViewWorkLogSupport(SpecialWorkLogSupportInterface $workLogSupport, User $user, TokenInterface $token): bool
    {
        return $workLogSupport->getWorkLog()->getWorkMonth()->getUser() === $user
            || in_array($user, $workLogSupport->getWorkLog()->getWorkMonth()->getUser()->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
    }

    private function canViewWorkMonth(WorkMonth $workMonth, User $user, TokenInterface $token): bool
    {
        return $workMonth->getUser() === $user
            || in_array($user, $workMonth->getUser()->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
    }

    /**
     * @param mixed $subject
     */
    private function canEdit($subject, User $user, TokenInterface $token): bool
    {
        if ($subject instanceof Contract) {
            return $this->canEditContract($subject, $user, $token);
        }

        if ($subject instanceof SupervisorWorkLogInterface) {
            return $this->canEditSupervisorWorkLog($subject, $user, $token);
        }

        if ($subject instanceof User) {
            return $this->canEditUser($subject, $user, $token);
        }

        if ($subject instanceof Vacation) {
            return $this->canEditVacation($subject, $user, $token);
        }

        if ($subject instanceof WorkLogInterface) {
            return $this->canEditWorkLog($subject, $user);
        }

        if ($subject instanceof SpecialWorkLogSupportInterface) {
            return $this->canEditWorkLogSupport($subject, $user, $token);
        }

        if ($subject instanceof WorkMonth) {
            return $this->canEditWorkMonth($subject, $user, $token);
        }

        return false;
    }

    private function canEditContract(Contract $contract, User $user, TokenInterface $token): bool
    {
        try {
            return $contract->getUser() === $user || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
        } catch (\TypeError $e) {
            return true;
        }
    }

    private function canEditSupervisorWorkLog(SupervisorWorkLogInterface $workLog, User $user, TokenInterface $token): bool
    {
        try {
            return $workLog->getWorkMonth()->getUser()->getId() !== $user->getId()
                && (
                    in_array($user, $workLog->getWorkMonth()->getUser()->getAllSupervisors())
                    || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN])
                );
        } catch (\TypeError $e) {
            return true;
        }
    }

    private function canEditUser(User $subject, User $user, TokenInterface $token): bool
    {
        return $subject->getId() === $user->getId() || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    private function canEditVacation(Vacation $vacation, User $user, TokenInterface $token): bool
    {
        try {
            return $vacation->getUser() === $user || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
        } catch (\TypeError $e) {
            return true;
        }
    }

    private function canEditWorkLog(WorkLogInterface $workLog, User $user): bool
    {
        try {
            return $workLog->getWorkMonth()->getUser() === $user;
        } catch (\TypeError $e) {
            return true;
        }
    }

    private function canEditWorkLogSupport(SpecialWorkLogSupportInterface $workLogSupport, User $user, TokenInterface $token): bool
    {
        return in_array($user, $workLogSupport->getWorkLog()->getWorkMonth()->getUser()->getAllSupervisors())
            || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
    }

    private function canEditWorkMonth(WorkMonth $workMonth, User $user, TokenInterface $token): bool
    {
        try {
            return $workMonth->getUser() === $user
                || in_array($user, $workMonth->getUser()->getAllSupervisors())
                || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
        } catch (\TypeError $e) {
            return true;
        }
    }
}
