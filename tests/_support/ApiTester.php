<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function createBusinessTripWorkLog(array $params = [])
    {
        $businessTripWorkLog = $this->populateEntity(new \App\Entity\BusinessTripWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'purpose' => 'Event',
            'destination' => 'Prague',
            'transport' => 'Plane',
            'expectedDeparture' => 'At the morning',
            'expectedArrival' => 'In the evening',
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($businessTripWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\BusinessTripWorkLog::class, [
            'id' => $businessTripWorkLog->getId(),
        ]);
    }

    public function createHomeOfficeWorkLog(array $params = [])
    {
        $homeOfficeWorkLog = $this->populateEntity(new \App\Entity\HomeOfficeWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'comment' => 'Comment',
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($homeOfficeWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\HomeOfficeWorkLog::class, [
            'id' => $homeOfficeWorkLog->getId(),
        ]);
    }

    public function createOvertimeWorkLog(array $params = [])
    {
        $overtimeWorkLog = $this->populateEntity(new \App\Entity\OvertimeWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'reason' => 'Reason',
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($overtimeWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\OvertimeWorkLog::class, [
            'id' => $overtimeWorkLog->getId(),
        ]);
    }

    public function createSickDayWorkLog(array $params = [])
    {
        $sickDayWorkLog = $this->populateEntity(new \App\Entity\SickDayWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'childName' => 'Jan Novak',
            'childDateOfBirth' => new DateTimeImmutable(),
            'variant' => 'WITH_NOTE',
        ], $params);

        $this->persistEntity($sickDayWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\SickDayWorkLog::class, [
            'id' => $sickDayWorkLog->getId(),
        ]);
    }

    public function createTimeOffWorkLog(array $params = [])
    {
        $timeOffWorkLog = $this->populateEntity(new \App\Entity\TimeOffWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'comment' => 'Comment',
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($timeOffWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\TimeOffWorkLog::class, [
            'id' => $timeOffWorkLog->getId(),
        ]);
    }

    public function createUserYearStats(array $params = [])
    {
        $userYearStats = $this->populateEntity(new \App\Entity\UserYearStats(), [
            'user' => function () {
                return $this->createUser();
            },
            'year' => 2018,
            'workedHours' => 0,
            'requiredHours' => 0,
            'vacationDaysUsed' => 0,
        ], $params);

        $this->persistEntity($userYearStats);

        return $this->grabEntityFromRepository(\App\Entity\UserYearStats::class, [
            'id' => $userYearStats->getId(),
        ]);
    }

    public function createVacationWorkLog(array $params = [])
    {
        $vacationWorkLog = $this->populateEntity(new \App\Entity\VacationWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($vacationWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\VacationWorkLog::class, [
            'id' => $vacationWorkLog->getId(),
        ]);
    }

    public function createWorkLog(array $params = [])
    {
        $workLog = $this->populateEntity(new \App\Entity\WorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'startTime' => new DateTimeImmutable(),
            'endTime' => (new DateTimeImmutable())->add(new DateInterval('PT1S')),
        ], $params);

        $this->persistEntity($workLog);

        return $this->grabEntityFromRepository(\App\Entity\WorkLog::class, [
            'id' => $workLog->getId(),
        ]);
    }

    public function createWorkMonth(array $params = [])
    {
        $workLog = $this->populateEntity(new \App\Entity\WorkMonth(), [
            'month' => 1,
            'status' => 'OPENED',
            'user'=> function() {
                return $this->createUser();
            },
            'year' => 2018,
        ], $params);

        $this->persistEntity($workLog);

        return $this->grabEntityFromRepository(\App\Entity\WorkMonth::class, [
            'id' => $workLog->getId(),
        ]);
    }

    public function createUser(array $params = [])
    {
        $user = $this->populateEntity(new \App\Entity\User(), [
            'apiToken' => null,
            'email' => 'user@example.com',
            'employeeId' => 'some_id_123',
            'firstName' => 'Jan',
            'lastName' => 'Novák',
            'isActive' => true,
            'plainPassword' => 'password',
            'supervisor' => null,
            'vacationDays' => 20,
            'resetPasswordToken' => null,
            'workHours' => function() {
                $workHours = [];

                foreach ($this->generateWorkHours(6) as $generatedWorkHour) {
                    $workHours[] = (new \App\Entity\WorkHours())
                        ->setMonth($generatedWorkHour['month'])
                        ->setYear($generatedWorkHour['year'])
                        ->setRequiredHours($generatedWorkHour['requiredHours']);
                }

                return $workHours;
            },
            'roles' => [\App\Entity\User::ROLE_EMPLOYEE],
        ], $params);

        $this->persistEntity($user);

        return $this->grabEntityFromRepository(\App\Entity\User::class, [
            'id' => $user->getId(),
        ]);
    }

    public function generateWorkHours($requiredHours) {
        $workHours = [];

        for ($year = 2018; $year <= 2020; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $workHours[] = [
                    'month' => $month,
                    'requiredHours' => $requiredHours,
                    'year' => $year,
                ];
            }
        }

        return $workHours;
    }

    /**
     * @param object $entity
     * @param array $defaultData
     * @param array $params
     * @return object
     */
    private function populateEntity($entity, array $defaultData, array $params)
    {
        foreach ($defaultData as $property => $value) {
            if (isset($params[$property])) {
                if ($params[$property] instanceof Closure) {
                    $newValue = call_user_func($params[$property]);
                } else {
                    $newValue = $params[$property];
                }
            } else {
                if ($value instanceof Closure) {
                    $newValue = call_user_func($value);
                } else {
                    $newValue = $value;
                }
            }

            $entity->{'set' . ucfirst($property)}($newValue);
        }

        return $entity;
    }
}
