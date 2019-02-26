<?php

namespace api\WorkMonth;

use App\Entity\User;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GetRecentSpecialApprovalCest
{
    private function beforeEmployee(\ApiTester $I)
    {
        $prophet = new Prophet();
        $user = $I->createUser();
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    private function beforeSuperAdmin(\ApiTester $I)
    {
        $prophet = new Prophet();
        $user = $I->createUser(['roles' => [User::ROLE_SUPER_ADMIN]]);
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    public function testGetEmpty(\ApiTester $I)
    {
        $this->beforeEmployee($I);

        $user1 = $I->createUser([
            'email' => 'user1@example.com',
            'employeeId' => 'id123',
        ]);
        $user2 = $I->createUser([
            'email' => 'user2@example.com',
            'employeeId' => 'id456',
            'supervisor' => $user1,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/recent_special_approvals/%d', $user2->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([]);
    }

    public function testGetAllAsSupervisor(\ApiTester $I)
    {
        $this->beforeEmployee($I);

        $user1 = $I->createUser(['email' => 'user2@example.com', 'employeeId' => 'id123']);
        $user2 = $I->createUser([
            'email' => 'user1@example.com',
            'employeeId' => 'id456',
            'supervisor' => $user1,
        ]);

        $dateTime1 = new \DateTimeImmutable();
        $dateTime2 = $this->subMonth($dateTime1);
        $dateTime3 = $this->subMonth($dateTime2);

        $workMonth1 = $I->createWorkMonth([
            'month' => $dateTime1->format('m'),
            'year' => $I->getSupportedYear($dateTime1->format('Y')),
            'user' => $user1,
        ]);
        $workMonth2 = $I->createWorkMonth([
            'month' => $dateTime1->format('m'),
            'year' => $I->getSupportedYear($dateTime1->format('Y')),
            'user' => $user2,
        ]);
        $workMonth3 = $I->createWorkMonth([
            'month' => $dateTime2->format('m'),
            'year' => $I->getSupportedYear($dateTime2->format('Y')),
            'user' => $user2,
        ]);
        $workMonth4 = $I->createWorkMonth([
            'month' => $dateTime3->format('m'),
            'year' => $I->getSupportedYear($dateTime3->format('Y')),
            'user' => $user2,
        ]);

        $businessTripWorkLog1 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $businessTripWorkLog2 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $businessTripWorkLog3 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $businessTripWorkLog4 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $businessTripWorkLog5 = $I->createBusinessTripWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $businessTripWorkLog6 = $I->createBusinessTripWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $homeOfficeWorkLog1 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $homeOfficeWorkLog2 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $homeOfficeWorkLog3 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $homeOfficeWorkLog4 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $homeOfficeWorkLog5 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $homeOfficeWorkLog6 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $overTimeWorkLog1 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $overTimeWorkLog2 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $overTimeWorkLog3 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $overTimeWorkLog4 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $overTimeWorkLog5 = $I->createOvertimeWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $overTimeWorkLog6 = $I->createOvertimeWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $timeOffWorkLog1 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $timeOffWorkLog2 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $timeOffWorkLog3 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $timeOffWorkLog4 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $timeOffWorkLog5 = $I->createTimeOffWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $timeOffWorkLog6 = $I->createTimeOffWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $vacationWorkLog1 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $vacationWorkLog2 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $vacationWorkLog3 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $vacationWorkLog4 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $vacationWorkLog5 = $I->createVacationWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $vacationWorkLog6 = $I->createVacationWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/recent_special_approvals/%s', $user1->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'businessTripWorkLogs' => [
                [
                    'date' => $businessTripWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'homeOfficeWorkLogs' => [
                [
                    'date' => $homeOfficeWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'overtimeWorkLogs' => [
                [
                    'date' => $overTimeWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'timeOffWorkLogs' => [
                [
                    'date' => $timeOffWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'vacationWorkLogs' => [
                [
                    'date' => $vacationWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
        ]);
        $I->dontSeeResponseContainsJson([
            'businessTripWorkLogs' => [
                ['id' => $businessTripWorkLog4->getId()],
                ['id' => $businessTripWorkLog6->getId()],
            ],
            'homeOfficeWorkLogs' => [
                ['id' => $homeOfficeWorkLog4->getId()],
                ['id' => $homeOfficeWorkLog6->getId()],
            ],
            'overTimeWorkLogs' => [
                ['id' => $overTimeWorkLog4->getId()],
                ['id' => $overTimeWorkLog6->getId()],
            ],
            'timeOffWorkLogs' => [
                ['id' => $timeOffWorkLog4->getId()],
                ['id' => $timeOffWorkLog6->getId()],
            ],
            'vacationWorkLogs' => [
                ['id' => $vacationWorkLog4->getId()],
                ['id' => $vacationWorkLog6->getId()],
            ],
        ]);
    }

    public function testGetAllAsSuperAdmin(\ApiTester $I)
    {
        $this->beforeSuperAdmin($I);

        $user1 = $I->createUser(['email' => 'user2@example.com', 'employeeId' => 'id123']);
        $user2 = $I->createUser([
            'email' => 'user1@example.com',
            'employeeId' => 'id456',
            'supervisor' => $user1,
        ]);

        $dateTime1 = new \DateTimeImmutable();
        $dateTime2 = $this->subMonth($dateTime1);
        $dateTime3 = $this->subMonth($dateTime2);

        $workMonth1 = $I->createWorkMonth([
            'month' => $dateTime1->format('m'),
            'year' => $I->getSupportedYear($dateTime1->format('Y')),
            'user' => $user1,
        ]);
        $workMonth2 = $I->createWorkMonth([
            'month' => $dateTime1->format('m'),
            'year' => $I->getSupportedYear($dateTime1->format('Y')),
            'user' => $user2,
        ]);
        $workMonth3 = $I->createWorkMonth([
            'month' => $dateTime2->format('m'),
            'year' => $I->getSupportedYear($dateTime2->format('Y')),
            'user' => $user2,
        ]);
        $workMonth4 = $I->createWorkMonth([
            'month' => $dateTime3->format('m'),
            'year' => $I->getSupportedYear($dateTime3->format('Y')),
            'user' => $user2,
        ]);

        $businessTripWorkLog1 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $businessTripWorkLog2 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $businessTripWorkLog3 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $businessTripWorkLog4 = $I->createBusinessTripWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $businessTripWorkLog5 = $I->createBusinessTripWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $businessTripWorkLog6 = $I->createBusinessTripWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $homeOfficeWorkLog1 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $homeOfficeWorkLog2 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $homeOfficeWorkLog3 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $homeOfficeWorkLog4 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $homeOfficeWorkLog5 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $homeOfficeWorkLog6 = $I->createHomeOfficeWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $overTimeWorkLog1 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $overTimeWorkLog2 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $overTimeWorkLog3 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $overTimeWorkLog4 = $I->createOvertimeWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $overTimeWorkLog5 = $I->createOvertimeWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $overTimeWorkLog6 = $I->createOvertimeWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $timeOffWorkLog1 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $timeOffWorkLog2 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $timeOffWorkLog3 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $timeOffWorkLog4 = $I->createTimeOffWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $timeOffWorkLog5 = $I->createTimeOffWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $timeOffWorkLog6 = $I->createTimeOffWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $vacationWorkLog1 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $vacationWorkLog2 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'timeApproved' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $vacationWorkLog3 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'timeRejected' => $dateTime1,
            'workMonth' => $workMonth2,
        ]);
        $vacationWorkLog4 = $I->createVacationWorkLog([
            'date' => $dateTime1,
            'workMonth' => $workMonth1,
        ]);
        $vacationWorkLog5 = $I->createVacationWorkLog([
            'date' => $dateTime2,
            'workMonth' => $workMonth3,
        ]);
        $vacationWorkLog6 = $I->createVacationWorkLog([
            'date' => $dateTime3,
            'workMonth' => $workMonth4,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/recent_special_approvals/%s', $user1->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'businessTripWorkLogs' => [
                [
                    'date' => $businessTripWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog4->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog4->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth1->getId(),
                        'month' => $workMonth1->getMonth(),
                        'status' => $workMonth1->getStatus(),
                        'user' => [
                            'email' => $user1->getEmail(),
                            'firstName' => $user1->getFirstName(),
                            'lastName' => $user1->getLastName(),
                            'id' => $user1->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth1->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $businessTripWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'homeOfficeWorkLogs' => [
                [
                    'date' => $homeOfficeWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog4->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog4->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth1->getId(),
                        'month' => $workMonth1->getMonth(),
                        'status' => $workMonth1->getStatus(),
                        'user' => [
                            'email' => $user1->getEmail(),
                            'firstName' => $user1->getFirstName(),
                            'lastName' => $user1->getLastName(),
                            'id' => $user1->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth1->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $homeOfficeWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'overtimeWorkLogs' => [
                [
                    'date' => $overTimeWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog4->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog4->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth1->getId(),
                        'month' => $workMonth1->getMonth(),
                        'status' => $workMonth1->getStatus(),
                        'user' => [
                            'email' => $user1->getEmail(),
                            'firstName' => $user1->getFirstName(),
                            'lastName' => $user1->getLastName(),
                            'id' => $user1->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth1->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $overTimeWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $overTimeWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'timeOffWorkLogs' => [
                [
                    'date' => $timeOffWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog4->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog4->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth1->getId(),
                        'month' => $workMonth1->getMonth(),
                        'status' => $workMonth1->getStatus(),
                        'user' => [
                            'email' => $user1->getEmail(),
                            'firstName' => $user1->getFirstName(),
                            'lastName' => $user1->getLastName(),
                            'id' => $user1->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth1->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $timeOffWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
            'vacationWorkLogs' => [
                [
                    'date' => $vacationWorkLog1->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog1->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog2->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog2->getId(),
                    'timeApproved' => $dateTime1->format(\DateTime::RFC3339),
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog3->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog3->getId(),
                    'timeApproved' => null,
                    'timeRejected' => $dateTime1->format(\DateTime::RFC3339),
                    'workMonth' => [
                        'id' => $workMonth2->getId(),
                        'month' => $workMonth2->getMonth(),
                        'status' => $workMonth2->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth2->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog4->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog4->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth1->getId(),
                        'month' => $workMonth1->getMonth(),
                        'status' => $workMonth1->getStatus(),
                        'user' => [
                            'email' => $user1->getEmail(),
                            'firstName' => $user1->getFirstName(),
                            'lastName' => $user1->getLastName(),
                            'id' => $user1->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth1->getYear()->getYear(),
                        ],
                    ],
                ],
                [
                    'date' => $vacationWorkLog5->getDate()->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog5->getId(),
                    'timeApproved' => null,
                    'timeRejected' => null,
                    'workMonth' => [
                        'id' => $workMonth3->getId(),
                        'month' => $workMonth3->getMonth(),
                        'status' => $workMonth3->getStatus(),
                        'user' => [
                            'email' => $user2->getEmail(),
                            'firstName' => $user2->getFirstName(),
                            'lastName' => $user2->getLastName(),
                            'id' => $user2->getId(),
                        ],
                        'year' => [
                            'year' => $workMonth3->getYear()->getYear(),
                        ],
                    ],
                ],
            ],
        ]);
        $I->dontSeeResponseContainsJson([
            'businessTripWorkLogs' => [
                ['id' => $businessTripWorkLog6->getId()],
            ],
            'homeOfficeWorkLogs' => [
                ['id' => $homeOfficeWorkLog6->getId()],
            ],
            'overTimeWorkLogs' => [
                ['id' => $overTimeWorkLog6->getId()],
            ],
            'timeOffWorkLogs' => [
                ['id' => $timeOffWorkLog6->getId()],
            ],
            'vacationWorkLogs' => [
                ['id' => $vacationWorkLog6->getId()],
            ],
        ]);
    }

    private function subMonth(\DateTimeImmutable $date)
    {
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        --$month;

        if ($month < 1) {
            $month = 12;
            --$year;
        }

        return $date->setDate($year, $month, 1);
    }
}
