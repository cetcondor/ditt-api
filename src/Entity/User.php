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
     * @var Vacation[]|Collection
     */
    private $vacations;

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

    /**
     * @var UserNotifications
     */
    private $notifications;

    /**
     * @var string|null
     */
    private $apiToken;

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
        $this->vacations = new ArrayCollection();
        $this->workHours = new ArrayCollection();
        $this->workMonths = new ArrayCollection();
        $this->yearStats = new ArrayCollection();
        $this->setNotifications(new UserNotifications());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function setEmployeeId(string $employeeId): User
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

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

    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): User
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

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

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): User
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(?User $supervisor): User
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getAllSupervisors(): array
    {
        $supervisors = [];

        if ($this->getSupervisor() !== null) {
            $supervisors[] = $this->getSupervisor();
            $supervisors = array_merge($supervisors, $this->getSupervisor()->getAllSupervisors());
        }

        return $supervisors;
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
     */
    public function setSupervised($supervised): User
    {
        $this->supervised = $supervised;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getAllSupervised(): array
    {
        $supervised = $this->getSupervised();

        foreach ($supervised as $supervisedUser) {
            $supervised = array_merge($supervised, $supervisedUser->getAllSupervised());
        }

        return $supervised;
    }

    /**
     * @return Vacation[]
     */
    public function getVacations()
    {
        if ($this->vacations instanceof Collection) {
            return $this->vacations->toArray();
        }

        return $this->vacations;
    }

    /**
     * @param Vacation[]|Collection $vacations
     */
    public function setVacations($vacations): User
    {
        foreach ($vacations as $vacation) {
            $vacation->setUser($this);
        }

        $this->vacations = $vacations;

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
     */
    public function setWorkHours($workHours): User
    {
        foreach ($workHours as $workHour) {
            $workHour->setUser($this);
        }

        $this->workHours = $workHours;

        return $this;
    }

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

    public function getNotifications(): UserNotifications
    {
        return $this->notifications;
    }

    public function setNotifications(UserNotifications $notifications): User
    {
        $this->notifications = $notifications;
        $this->notifications->setUser($this);

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): User
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function renewApiToken(): User
    {
        $this->apiToken = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(128))), 0, 128);

        return $this;
    }
}
