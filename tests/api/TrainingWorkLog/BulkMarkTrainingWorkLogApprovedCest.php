<?php

namespace api\TrainingWorkLog;

use App\Entity\TrainingWorkLog;
use Symfony\Component\HttpFoundation\Response;

class BulkMarkTrainingWorkLogApprovedCest
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
    public function testMarkApproved(\ApiTester $I): void
    {
        $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => '123', 'supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createTrainingWorkLog([
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createTrainingWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/training_work_logs/bulk/mark_approved',
            ['workLogIds' => [$workLog->getId(), $workLog2->getId()]]
        );

        // $I->seeEmailIsSent(1);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            ['id' => $workLog->getId()],
            ['id' => $workLog2->getId()],
        ]);
        $workLog = $I->grabEntityFromRepository(TrainingWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $workLog2 = $I->grabEntityFromRepository(TrainingWorkLog::class, [
            'id' => $workLog2->getId(),
        ]);
        $I->assertNotNull($workLog->getTimeApproved());
        $I->assertNull($workLog->getTimeRejected());
        $I->assertNotNull($workLog2->getTimeApproved());
        $I->assertNull($workLog2->getTimeRejected());
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => '123', 'supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createTrainingWorkLog([
            'timeApproved' => $time,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createTrainingWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/training_work_logs/bulk/mark_approved',
            ['workLogIds' => [$workLog->getId(), $workLog2->getId()]]
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => sprintf('Work log with id %d has been already approved.', $workLog->getId()),
        ]);
        $I->grabEntityFromRepository(TrainingWorkLog::class, [
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
        $workLog = $I->createTrainingWorkLog([
            'timeRejected' => $time,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createTrainingWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/training_work_logs/bulk/mark_approved',
            ['workLogIds' => [$workLog->getId(), $workLog2->getId()]]
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => sprintf('Work log with id %d has been already rejected.', $workLog->getId()),
        ]);
        $I->grabEntityFromRepository(TrainingWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
    }
}
