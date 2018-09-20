<?php

namespace api\WorkLog;

use App\Entity\User;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GetWorkMonthDetailCest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var User|null
     */
    private $supervisor;

    private function beforeEmployee(\ApiTester $I)
    {
        $prophet = new Prophet();
        $this->user = $I->createUser();
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    private function beforeSupervisor(\ApiTester $I)
    {
        $prophet = new Prophet();
        $this->supervisor = $I->createUser();
        $this->user = $I->createUser([
            'employeeId' => '123',
            'email' => 'user2@example.com',
            'supervisor' => $this->supervisor,
        ]);
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->supervisor);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    public function testGetOpenedDetail(\ApiTester $I)
    {
        $this->beforeEmployee($I);

        $startTime1 = new \DateTimeImmutable();
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = new \DateTimeImmutable();
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $workMonth = $I->createWorkMonth([
            'month' => $startTime1->format('m'),
            'user' => $this->user,
            'year' => $startTime1->format('Y'),
        ]);

        $businessTripWorkLog1 = $I->createBusinessTripWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $businessTripWorkLog2 = $I->createBusinessTripWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog1 = $I->createHomeOfficeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog2 = $I->createHomeOfficeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog1 = $I->createOvertimeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog2 = $I->createOvertimeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog1 = $I->createSickDayWorkLog([
            'date' => $startTime1,
            'variant' => 'WITHOUT_NOTE',
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog2 = $I->createSickDayWorkLog([
            'date' => $endTime1,
            'variant' => 'SICK_CHILD',
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog1 = $I->createTimeOffWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog2 = $I->createTimeOffWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog1 = $I->createVacationWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog2 = $I->createVacationWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog1 = $I->createWorkLog([
            'startTime' => $startTime1,
            'endTime' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createWorkLog([
            'startTime' => $startTime2,
            'endTime' => $endTime2,
            'workMonth' => $workMonth,
        ]);

        $workMonth->setBusinessTripWorkLogs([$businessTripWorkLog1, $businessTripWorkLog2]);
        $workMonth->setHomeOfficeWorkLogs([$homeOfficeWorkLog1, $homeOfficeWorkLog2]);
        $workMonth->setOvertimeWorkLogs([$overtimeWorkLog1, $overtimeWorkLog2]);
        $workMonth->setSickDayWorkLogs([$sickDayWorkLog1, $sickDayWorkLog2]);
        $workMonth->setTimeOffWorkLogs([$timeOffWorkLog1, $timeOffWorkLog2]);
        $workMonth->setVacationWorkLogs([$vacationWorkLog1, $vacationWorkLog2]);
        $workMonth->setWorkLogs([$workLog1, $workLog2]);
        $I->flushToDatabase();

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/work_months/%d/detail', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'businessTripWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog2->getId(),
                ],
            ],
            'homeOfficeWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog2->getId(),
                ],
            ],
            'month' => $workMonth->getMonth(),
            'overtimeWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $overtimeWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $overtimeWorkLog2->getId(),
                ],
            ],
            'status' => 'OPENED',
            'sickDayWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $sickDayWorkLog1->getId(),
                    'variant' => $sickDayWorkLog1->getVariant(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $sickDayWorkLog2->getId(),
                    'variant' => $sickDayWorkLog2->getVariant(),
                ],
            ],
            'timeOffWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog2->getId(),
                ],
            ],
            'user' => ['id' => $this->user->getId()],
            'vacationWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog2->getId(),
                ],
            ],
            'workLogs' => [
                [
                    'startTime' => $startTime1->format(\DateTime::RFC3339),
                    'endTime' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $workLog1->getId(),
                ],
                [
                    'startTime' => $startTime2->format(\DateTime::RFC3339),
                    'endTime' => $endTime2->format(\DateTime::RFC3339),
                    'id' => $workLog2->getId(),
                ],
            ],
            'year' => $workMonth->getYear(),
        ]);
    }

    public function testGetWaitingForApprovalDetail(\ApiTester $I)
    {
        $this->beforeEmployee($I);

        $startTime1 = new \DateTimeImmutable();
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = new \DateTimeImmutable();
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $workMonth = $I->createWorkMonth([
            'month' => $startTime1->format('m'),
            'status' => 'WAITING_FOR_APPROVAL',
            'user' => $this->user,
            'year' => $startTime1->format('Y'),
        ]);

        $businessTripWorkLog1 = $I->createBusinessTripWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $businessTripWorkLog2 = $I->createBusinessTripWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog1 = $I->createHomeOfficeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog2 = $I->createHomeOfficeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog1 = $I->createOvertimeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog2 = $I->createOvertimeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog1 = $I->createSickDayWorkLog([
            'date' => $startTime1,
            'variant' => 'WITHOUT_NOTE',
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog2 = $I->createSickDayWorkLog([
            'date' => $endTime1,
            'variant' => 'SICK_CHILD',
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog1 = $I->createTimeOffWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog2 = $I->createTimeOffWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog1 = $I->createVacationWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog2 = $I->createVacationWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog1 = $I->createWorkLog([
            'startTime' => $startTime1,
            'endTime' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createWorkLog([
            'startTime' => $startTime2,
            'endTime' => $endTime2,
            'workMonth' => $workMonth,
        ]);

        $workMonth->setBusinessTripWorkLogs([$businessTripWorkLog1, $businessTripWorkLog2]);
        $workMonth->setHomeOfficeWorkLogs([$homeOfficeWorkLog1, $homeOfficeWorkLog2]);
        $workMonth->setOvertimeWorkLogs([$overtimeWorkLog1, $overtimeWorkLog2]);
        $workMonth->setSickDayWorkLogs([$sickDayWorkLog1, $sickDayWorkLog2]);
        $workMonth->setTimeOffWorkLogs([$timeOffWorkLog1, $timeOffWorkLog2]);
        $workMonth->setVacationWorkLogs([$vacationWorkLog1, $vacationWorkLog2]);
        $workMonth->setWorkLogs([$workLog1, $workLog2]);
        $I->flushToDatabase();

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/work_months/%d/detail', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'businessTripWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog2->getId(),
                ],
            ],
            'homeOfficeWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog2->getId(),
                ],
            ],
            'month' => $workMonth->getMonth(),
            'overtimeWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $overtimeWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $overtimeWorkLog2->getId(),
                ],
            ],
            'status' => 'WAITING_FOR_APPROVAL',
            'sickDayWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $sickDayWorkLog1->getId(),
                    'variant' => $sickDayWorkLog1->getVariant(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $sickDayWorkLog2->getId(),
                    'variant' => $sickDayWorkLog2->getVariant(),
                ],
            ],
            'timeOffWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog2->getId(),
                ],
            ],
            'user' => ['id' => $this->user->getId()],
            'vacationWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog2->getId(),
                ],
            ],
            'workLogs' => [
                [
                    'startTime' => $startTime1->format(\DateTime::RFC3339),
                    'endTime' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $workLog1->getId(),
                ],
                [
                    'startTime' => $startTime2->format(\DateTime::RFC3339),
                    'endTime' => $endTime2->format(\DateTime::RFC3339),
                    'id' => $workLog2->getId(),
                ],
            ],
            'year' => $workMonth->getYear(),
        ]);
    }

    public function testGetOpenedDetailAsSupervisor(\ApiTester $I)
    {
        $this->beforeSupervisor($I);

        $startTime1 = new \DateTimeImmutable();
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = new \DateTimeImmutable();
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $workMonth = $I->createWorkMonth([
            'month' => $startTime1->format('m'),
            'user' => $this->user,
            'year' => $startTime1->format('Y'),
        ]);

        $businessTripWorkLog1 = $I->createBusinessTripWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $businessTripWorkLog2 = $I->createBusinessTripWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog1 = $I->createHomeOfficeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog2 = $I->createHomeOfficeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog1 = $I->createOvertimeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog2 = $I->createOvertimeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog1 = $I->createSickDayWorkLog([
            'date' => $startTime1,
            'variant' => 'WITHOUT_NOTE',
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog2 = $I->createSickDayWorkLog([
            'date' => $endTime1,
            'variant' => 'SICK_CHILD',
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog1 = $I->createTimeOffWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog2 = $I->createTimeOffWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog1 = $I->createVacationWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog2 = $I->createVacationWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog1 = $I->createWorkLog([
            'startTime' => $startTime1,
            'endTime' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createWorkLog([
            'startTime' => $startTime2,
            'endTime' => $endTime2,
            'workMonth' => $workMonth,
        ]);

        $workMonth->setBusinessTripWorkLogs([$businessTripWorkLog1, $businessTripWorkLog2]);
        $workMonth->setHomeOfficeWorkLogs([$homeOfficeWorkLog1, $homeOfficeWorkLog2]);
        $workMonth->setOvertimeWorkLogs([$overtimeWorkLog1, $overtimeWorkLog2]);
        $workMonth->setSickDayWorkLogs([$sickDayWorkLog1, $sickDayWorkLog2]);
        $workMonth->setTimeOffWorkLogs([$timeOffWorkLog1, $timeOffWorkLog2]);
        $workMonth->setVacationWorkLogs([$vacationWorkLog1, $vacationWorkLog2]);
        $workMonth->setWorkLogs([$workLog1, $workLog2]);
        $I->flushToDatabase();

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/work_months/%d/detail', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'businessTripWorkLogs' => [],
            'homeOfficeWorkLogs' => [],
            'month' => $workMonth->getMonth(),
            'overtimeWorkLogs' => [],
            'status' => 'OPENED',
            'sickDayWorkLogs' => [],
            'timeOffWorkLogs' => [],
            'user' => ['id' => $this->user->getId()],
            'vacationWorkLogs' => [],
            'workLogs' => [],
            'year' => $workMonth->getYear(),
        ]);
    }

    public function testGetWaitingForApprovalDetailAsSupervisor(\ApiTester $I)
    {
        $this->beforeSupervisor($I);

        $startTime1 = new \DateTimeImmutable();
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = new \DateTimeImmutable();
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $workMonth = $I->createWorkMonth([
            'month' => $startTime1->format('m'),
            'status' => 'WAITING_FOR_APPROVAL',
            'user' => $this->user,
            'year' => $startTime1->format('Y'),
        ]);

        $businessTripWorkLog1 = $I->createBusinessTripWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $businessTripWorkLog2 = $I->createBusinessTripWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog1 = $I->createHomeOfficeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $homeOfficeWorkLog2 = $I->createHomeOfficeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog1 = $I->createOvertimeWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $overtimeWorkLog2 = $I->createOvertimeWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog1 = $I->createSickDayWorkLog([
            'date' => $startTime1,
            'variant' => 'WITHOUT_NOTE',
            'workMonth' => $workMonth,
        ]);
        $sickDayWorkLog2 = $I->createSickDayWorkLog([
            'date' => $endTime1,
            'variant' => 'SICK_CHILD',
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog1 = $I->createTimeOffWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $timeOffWorkLog2 = $I->createTimeOffWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog1 = $I->createVacationWorkLog([
            'date' => $startTime1,
            'workMonth' => $workMonth,
        ]);
        $vacationWorkLog2 = $I->createVacationWorkLog([
            'date' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog1 = $I->createWorkLog([
            'startTime' => $startTime1,
            'endTime' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createWorkLog([
            'startTime' => $startTime2,
            'endTime' => $endTime2,
            'workMonth' => $workMonth,
        ]);

        $workMonth->setBusinessTripWorkLogs([$businessTripWorkLog1, $businessTripWorkLog2]);
        $workMonth->setHomeOfficeWorkLogs([$homeOfficeWorkLog1, $homeOfficeWorkLog2]);
        $workMonth->setOvertimeWorkLogs([$overtimeWorkLog1, $overtimeWorkLog2]);
        $workMonth->setSickDayWorkLogs([$sickDayWorkLog1, $sickDayWorkLog2]);
        $workMonth->setTimeOffWorkLogs([$timeOffWorkLog1, $timeOffWorkLog2]);
        $workMonth->setVacationWorkLogs([$vacationWorkLog1, $vacationWorkLog2]);
        $workMonth->setWorkLogs([$workLog1, $workLog2]);
        $I->flushToDatabase();

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/work_months/%d/detail', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'businessTripWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $businessTripWorkLog2->getId(),
                ],
            ],
            'homeOfficeWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $homeOfficeWorkLog2->getId(),
                ],
            ],
            'month' => $workMonth->getMonth(),
            'overtimeWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $overtimeWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $overtimeWorkLog2->getId(),
                ],
            ],
            'status' => 'WAITING_FOR_APPROVAL',
            'sickDayWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $sickDayWorkLog1->getId(),
                    'variant' => $sickDayWorkLog1->getVariant(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $sickDayWorkLog2->getId(),
                    'variant' => $sickDayWorkLog2->getVariant(),
                ],
            ],
            'timeOffWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $timeOffWorkLog2->getId(),
                ],
            ],
            'user' => ['id' => $this->user->getId()],
            'vacationWorkLogs' => [
                [
                    'date' => $startTime1->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog1->getId(),
                ],
                [
                    'date' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $vacationWorkLog2->getId(),
                ],
            ],
            'workLogs' => [
                [
                    'startTime' => $startTime1->format(\DateTime::RFC3339),
                    'endTime' => $endTime1->format(\DateTime::RFC3339),
                    'id' => $workLog1->getId(),
                ],
                [
                    'startTime' => $startTime2->format(\DateTime::RFC3339),
                    'endTime' => $endTime2->format(\DateTime::RFC3339),
                    'id' => $workLog2->getId(),
                ],
            ],
            'year' => $workMonth->getYear(),
        ]);
    }
}
