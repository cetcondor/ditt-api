<?php

namespace api\TimeOffWorkLog;

use App\Entity\SpecialLeaveWorkLog;
use Symfony\Component\HttpFoundation\Response;

class BulkMarkSpecialLeaveWorkLogRejectedCest
{
    /**
     * @var User|null
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $this->user = $I->createUser();
        $I->login($this->user);
    }

    /**
     * @throws \Exception
     */
    public function testMarkRejected(\ApiTester $I): void
    {
        $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => '123', 'supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createSpecialLeaveWorkLog([
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createSpecialLeaveWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/special_leave_work_logs/bulk/mark_rejected',
            [
                'rejectionMessage' => 'Rejection message.',
                'workLogIds' => [$workLog->getId(), $workLog2->getId()],
            ]
        );

        // $I->seeEmailIsSent(1);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            ['id' => $workLog->getId()],
            ['id' => $workLog2->getId()],
        ]);
        $workLog = $I->grabEntityFromRepository(SpecialLeaveWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $workLog2 = $I->grabEntityFromRepository(SpecialLeaveWorkLog::class, [
            'id' => $workLog2->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
        $I->assertNull($workLog2->getTimeApproved());
        $I->assertNotNull($workLog2->getTimeRejected());
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => '123', 'supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createSpecialLeaveWorkLog([
            'timeApproved' => $time,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createSpecialLeaveWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/special_leave_work_logs/bulk/mark_rejected',
            [
                'rejectionMessage' => 'Rejection message.',
                'workLogIds' => [$workLog->getId(), $workLog2->getId()],
            ]
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => sprintf('Work log with id %d has been already approved.', $workLog->getId()),
        ]);
        $I->grabEntityFromRepository(SpecialLeaveWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNotNull($workLog->getTimeApproved());
        $I->assertNull($workLog->getTimeRejected());
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedRejected(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => '123', 'supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createSpecialLeaveWorkLog([
            'timeRejected' => $time,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createSpecialLeaveWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/special_leave_work_logs/bulk/mark_rejected',
            [
                'rejectionMessage' => 'Rejection message.',
                'workLogIds' => [$workLog->getId(), $workLog2->getId()],
            ]
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => sprintf('Work log with id %d has been already rejected.', $workLog->getId()),
        ]);
        $I->grabEntityFromRepository(SpecialLeaveWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
    }
}
