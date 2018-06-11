<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\WorkHours;
use App\Entity\WorkLog;
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

        if ($subject instanceof WorkHours) {
            return true;
        }

        if ($subject instanceof WorkLog) {
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

        if ($this->decisionManager->decide($token, [User::ROLE_SUPER_ADMIN])) {
            return true;
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

        if ($subject instanceof WorkHours) {
            return $this->canViewWorkHour($subject, $user, $token);
        }

        if ($subject instanceof WorkLog) {
            return $this->canViewWorkLog($subject, $user);
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
     * @param WorkHours $workHours
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    private function canViewWorkHour(WorkHours $workHours, User $user, TokenInterface $token): bool
    {
        return $workHours->getUser() === $user || $this->decisionManager->decide($token, [User::ROLE_ADMIN]);
    }

    /**
     * @param WorkLog $workLog
     * @param User $user
     * @return bool
     */
    private function canViewWorkLog(WorkLog $workLog, User $user): bool
    {
        return $workLog->getUser() === $user;
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

        if ($subject instanceof WorkHours) {
            return $this->canEditWorkHours($subject, $user, $token);
        }

        if ($subject instanceof WorkLog) {
            return $this->canEditWorkLog($subject, $user);
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
     * @param WorkLog $workLog
     * @param User $user
     * @return bool
     */
    private function canEditWorkLog(WorkLog $workLog, User $user): bool
    {
        try {
            return $workLog->getUser() === $user;
        } catch (\TypeError $e) {
            return true;
        }
    }
}
