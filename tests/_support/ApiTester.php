<?php

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

    public function login($user, $firewallName = 'main') {
        $this->amLoggedInAs($user);
        $this->grabService('security.token_storage')->setToken(new UsernamePasswordToken(
            $user,
            null,
            $firewallName,
            $user->getRoles()
        ));
    }

    public function getSupportedYear($year) {
        return $this->grabEntityFromRepository(\App\Entity\SupportedYear::class, [
            'year' => $year,
        ]);
    }

    public function getSupportedYearNormalized($year) {
        return $this->grabEntityFromRepository(\App\Entity\SupportedYear::class, [
            'year' => $year,
        ]);
    }

    public function createBanWorkLog(array $params = [])
    {
        $banWorkLog = $this->populateEntity(new \App\Entity\BanWorkLog(), [
            'workTimeLimit' => (5.5 * 3600),
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
        ], $params);

        $this->persistEntity($banWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\BanWorkLog::class, [
            'id' => $banWorkLog->getId(),
        ]);
    }

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

    public function createMaternityProtectionWorkLog(array $params = [])
    {
        $maternityProtectionWorkLog = $this->populateEntity(new \App\Entity\MaternityProtectionWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
        ], $params);

        $this->persistEntity($maternityProtectionWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\MaternityProtectionWorkLog::class, [
            'id' => $maternityProtectionWorkLog->getId(),
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

    public function createParentalLeaveWorkLog(array $params = [])
    {
        $parentalLeaveWorkLog = $this->populateEntity(new \App\Entity\ParentalLeaveWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
        ], $params);

        $this->persistEntity($parentalLeaveWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\ParentalLeaveWorkLog::class, [
            'id' => $parentalLeaveWorkLog->getId(),
        ]);
    }

    public function createSickDayUnpaidWorkLog(array $params = [])
    {
        $sickDayUnpaidWorkLog = $this->populateEntity(new \App\Entity\SickDayUnpaidWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
        ], $params);

        $this->persistEntity($sickDayUnpaidWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\SickDayUnpaidWorkLog::class, [
            'id' => $sickDayUnpaidWorkLog->getId(),
        ]);
    }

    public function createSpecialLeaveWorkLog(array $params = [])
    {
        $specialLeaveWorkLog = $this->populateEntity(new \App\Entity\SpecialLeaveWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'reason' => 'Reason to leave',
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($specialLeaveWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\SpecialLeaveWorkLog::class, [
            'id' => $specialLeaveWorkLog->getId(),
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

    public function createTrainingWorkLog(array $params = [])
    {
        $trainingTripWorkLog = $this->populateEntity(new \App\Entity\TrainingWorkLog(), [
            'workMonth' => function () {
                return $this->createWorkMonth();
            },
            'date' => new DateTimeImmutable(),
            'title' => 'Title',
            'comment' => 'Comment',
            'timeApproved' => null,
            'timeRejected' => null,
        ], $params);

        $this->persistEntity($trainingTripWorkLog);

        return $this->grabEntityFromRepository(\App\Entity\TrainingWorkLog::class, [
            'id' => $trainingTripWorkLog->getId(),
        ]);
    }

    public function createUserYearStats(array $params = [])
    {
        $userYearStats = $this->populateEntity(new \App\Entity\UserYearStats(), [
            'user' => function () {
                return $this->createUser();
            },
            'year' => $this->getSupportedYear(2018),
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
            'year' => $this->getSupportedYear(2018),
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
            'resetPasswordToken' => null,
            'vacations' => function() {
                $vacations = [];

                foreach ($this->generateVacations(20, 0) as $generatedVacation) {
                    $vacations[] = (new \App\Entity\Vacation())
                        ->setYear($generatedVacation['year'])
                        ->setVacationDays($generatedVacation['vacationDays'])
                        ->setVacationDaysCorrection($generatedVacation['vacationDaysCorrection']);
                }

                return $vacations;
            },
            'workHours' => function() {
                $workHours = [];

                foreach ($this->generateWorkHours(21600) as $generatedWorkHour) {
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

    public function generateVacations($vacationDays, $vacationDaysCorrection) {
        $vacations = [];

        for ($year = 2018; $year <= 2024; ++$year) {
            $vacations[] = [
                'vacationDays' => $vacationDays,
                'vacationDaysCorrection' => $vacationDaysCorrection,
                'year' => $this->getSupportedYear($year),
            ];
        }

        return $vacations;
    }

    public function generateVacationsNormalized($vacationDays, $vacationDaysCorrection) {
        $vacations = [];

        for ($year = 2018; $year <= 2024; ++$year) {
            $vacations[] = [
                'vacationDays' => $vacationDays,
                'vacationDaysCorrection' => $vacationDaysCorrection,
                'year' => [
                    'year' => $year,
                ],
            ];
        }

        return $vacations;
    }

    public function generateVacationsNormalizedUri($vacationDays, $vacationDaysCorrection) {
        $vacations = [];

        for ($year = 2018; $year <= 2024; ++$year) {
            $vacations[] = [
                'vacationDays' => $vacationDays,
                'vacationDaysCorrection' => $vacationDaysCorrection,
                'year' => sprintf('/supported_years/%s', $year),
            ];
        }

        return $vacations;
    }

    public function generateWorkHours($requiredHours) {
        $workHours = [];

        for ($year = 2018; $year <= 2024; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $workHours[] = [
                    'month' => $month,
                    'requiredHours' => $requiredHours,
                    'year' => $this->getSupportedYear($year),
                ];
            }
        }

        return $workHours;
    }

    public function generateWorkHoursNormalized($requiredHours) {
        $workHours = [];

        for ($year = 2018; $year <= 2024; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $workHours[] = [
                    'month' => $month,
                    'requiredHours' => $requiredHours,
                    'year' => [
                        'year' => $year,
                    ],
                ];
            }
        }

        return $workHours;
    }

    public function generateWorkHoursNormalizedUri($requiredHours) {
        $workHours = [];

        for ($year = 2018; $year <= 2024; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $workHours[] = [
                    'month' => $month,
                    'requiredHours' => $requiredHours,
                    'year' => sprintf('/supported_years/%s', $year),
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
