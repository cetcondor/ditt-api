<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Vacation;
use App\Entity\WorkHours;
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
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if ($subject instanceof User) {
            return true;
        }

        if ($subject instanceof Vacation) {
            return true;
        }

        if ($subject instanceof WorkHours) {
            return true;
        }

        if ($subject instanceof WorkLogInterface) {
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
     * @param TokenInterface $token
     * @throws \LogicException
     * @return bool
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

        throw new \LogicException(
            sprintf('Invalid attribute for voter. Allowed are %s and %s', self::VIEW, self::EDIT)
        );
    }

    // VIEW

    /**
     * @param mixed $subject
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canView($subject, User $user, TokenInterface $token): bool
    {
        if ($subject instanceof User) {
            return $this->canViewUser($subject, $user, $token);
        }

        if ($subject instanceof Vacation) {
            return $this->canViewVacation($subject, $user, $token);
        }

        if ($subject instanceof WorkHours) {
            return $this->canViewWorkHour($subject, $user, $token);
        }

        if ($subject instanceof WorkLogInterface) {
            return $this->canViewWorkLog($subject, $user, $token);
        }

        if ($subject instanceof WorkMonth) {
            return $this->canViewWorkMonth($subject, $user, $token);
        }

        return false;
    }

    /**
     * @param User $subject
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canViewUser(User $subject, User $user, TokenInterface $token): bool
    {
        return $subject === $user
            || $subject->getSupervisor() === $user
            || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    /**
     * @param Vacation $vacation
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canViewVacation(Vacation $vacation, User $user, TokenInterface $token): bool
    {
        return $vacation->getUser() === $user
            || (
                $vacation->getUser()->getSupervisor()
                && $vacation->getUser()->getSupervisor() === $user
            ) || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    /**
     * @param WorkHours $workHours
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canViewWorkHour(WorkHours $workHours, User $user, TokenInterface $token): bool
    {
        return $workHours->getUser() === $user
            || (
                $workHours->getUser()->getSupervisor()
                && $workHours->getUser()->getSupervisor() === $user
            ) || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    /**
     * @param WorkLogInterface $workLog
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canViewWorkLog(WorkLogInterface $workLog, User $user, TokenInterface $token): bool
    {
        return $workLog->getWorkMonth()->getUser() === $user
            || (
                $workLog->getWorkMonth()->getUser()->getSupervisor()
                && $workLog->getWorkMonth()->getUser()->getSupervisor() === $user
            ) || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
    }

    /**
     * @param WorkMonth $workMonth
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canViewWorkMonth(WorkMonth $workMonth, User $user, TokenInterface $token): bool
    {
        return $workMonth->getUser() === $user
            || (
                $workMonth->getUser()->getSupervisor()
                && $workMonth->getUser()->getSupervisor() === $user
            ) || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
    }

    /**
     * @param mixed $subject
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canEdit($subject, User $user, TokenInterface $token): bool
    {
        if ($subject instanceof User) {
            return $this->canEditUser($subject, $user, $token);
        }

        if ($subject instanceof Vacation) {
            return $this->canEditVacation($subject, $user, $token);
        }

        if ($subject instanceof WorkHours) {
            return $this->canEditWorkHours($subject, $user, $token);
        }

        if ($subject instanceof WorkLogInterface) {
            return $this->canEditWorkLog($subject, $user);
        }

        if ($subject instanceof WorkMonth) {
            return $this->canEditWorkMonth($subject, $user, $token);
        }

        return false;
    }

    /**
     * @param User $subject
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canEditUser(User $subject, User $user, TokenInterface $token): bool
    {
        return $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    /**
     * @param Vacation $vacation
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canEditVacation(Vacation $vacation, User $user, TokenInterface $token): bool
    {
        try {
            return $vacation->getUser() === $user || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
        } catch (\TypeError $e) {
            return true;
        }
    }

    /**
     * @param WorkHours $workHours
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canEditWorkHours(WorkHours $workHours, User $user, TokenInterface $token): bool
    {
        try {
            return $workHours->getUser() === $user || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
        } catch (\TypeError $e) {
            return true;
        }
    }

    /**
     * @param WorkLogInterface $workLog
     * @param User $user
     * @return bool
     */
    private function canEditWorkLog(WorkLogInterface $workLog, User $user): bool
    {
        try {
            return $workLog->getWorkMonth()->getUser() === $user;
        } catch (\TypeError $e) {
            return true;
        }
    }

    /**
     * @param WorkMonth $workMonth
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canEditWorkMonth(WorkMonth $workMonth, User $user, TokenInterface $token): bool
    {
        try {
            return $workMonth->getUser() === $user
                || (
                    $workMonth->getUser()->getSupervisor()
                    && $workMonth->getUser()->getSupervisor() === $user
                ) || $this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN]);
        } catch (\TypeError $e) {
            return true;
        }
    }
}
