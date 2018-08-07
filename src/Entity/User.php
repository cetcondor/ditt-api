<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    const API_RESOURCE_URL = '/users';

    const ROLE_USER = 'ROLE_USER';
    const ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $employeeId;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $plainPassword;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var string[]
     */
    private $roles;

    /**
     * @var string|null
     */
    private $resetPasswordToken;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @var User[]|Collection
     */
    private $supervised;

    /**
     * @var int
     */
    private $vacationDays;

    /**
     * @var WorkHours[]|Collection
     */
    private $workHours;

    /**
     * @var WorkMonth[]|Collection
     */
    private $workMonths;

    /**
     * @var UserYearStats[]|Collection
     */
    private $yearStats;

    public function __construct()
    {
        $this->password = '';
        $this->plainPassword = null;
        $this->email = '';
        $this->employeeId = '';
        $this->firstName = '';
        $this->lastName = '';
        $this->roles = [self::ROLE_EMPLOYEE];
        $this->supervised = new ArrayCollection();
        $this->vacationDays = 0;
        $this->workHours = new ArrayCollection();
        $this->workMonths = new ArrayCollection();
        $this->yearStats = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    /**
     * @param string $employeeId
     * @return User
     */
    public function setEmployeeId(string $employeeId): User
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return User
     */
    public function setIsActive(bool $isActive): User
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param string $role
     * @return User
     */
    public function addRole(string $role): User
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    /**
     * @param string $roleToRemove
     * @return User
     */
    public function removeRole(string $roleToRemove): User
    {
        $keyToRemove = null;
        foreach ($this->roles as $key => $role) {
            if ($role === $roleToRemove) {
                $keyToRemove = $key;

                break;
            }
        }
        unset($this->roles[$keyToRemove]);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    /**
     * @param null|string $resetPasswordToken
     * @return User
     */
    public function setResetPasswordToken(?string $resetPasswordToken): User
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    /**
     * @param User|null $supervisor
     * @return User
     */
    public function setSupervisor(?User $supervisor): User
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getSupervised(): array
    {
        if ($this->supervised instanceof Collection) {
            return $this->supervised->toArray();
        }

        return $this->supervised;
    }

    /**
     * @param User[]|Collection $supervised
     * @return User
     */
    public function setSupervised($supervised): User
    {
        $this->supervised = $supervised;

        return $this;
    }

    /**
     * @return int
     */
    public function getVacationDays(): int
    {
        return $this->vacationDays;
    }

    /**
     * @param int $vacationDays
     * @return User
     */
    public function setVacationDays(int $vacationDays): User
    {
        $this->vacationDays = $vacationDays;

        return $this;
    }

    /**
     * @return WorkHours[]
     */
    public function getWorkHours()
    {
        if ($this->workHours instanceof Collection) {
            return $this->workHours->toArray();
        }

        return $this->workHours;
    }

    /**
     * @param WorkHours[]|Collection $workHours
     * @return User
     */
    public function setWorkHours($workHours): User
    {
        foreach ($workHours as $workHour) {
            $workHour->setUser($this);
        }

        $this->workHours = $workHours;

        return $this;
    }

    /**
     * @param WorkHours $workHours
     * @return User
     */
    public function addWorkHours(WorkHours $workHours): User
    {
        if ($this->workHours instanceof Collection) {
            $workHours->setUser($this);
            $this->workHours->add($workHours);
        } else {
            $this->workHours[] = $workHours;
        }

        return $this;
    }

    /**
     * @return WorkMonth[]
     */
    public function getWorkMonths()
    {
        if ($this->workMonths instanceof Collection) {
            return $this->workMonths->toArray();
        }

        return $this->workMonths;
    }

    /**
     * @param WorkMonth[]|Collection $workMonths
     * @return User
     */
    public function setWorkMonths($workMonths): User
    {
        $this->workMonths = $workMonths;

        return $this;
    }

    /**
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @return UserYearStats[]|Collection
     */
    public function getYearStats()
    {
        return $this->yearStats;
    }

    /**
     * @param UserYearStats[]|Collection $yearStats
     * @return User
     */
    public function setYearStats($yearStats)
    {
        $this->yearStats = $yearStats;

        return $this;
    }
}
