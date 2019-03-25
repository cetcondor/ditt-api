<?php

namespace api\VacationWorkLog;

use App\Entity\VacationWorkLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BulkMarkVacationWorkLogApprovedCest
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @param \ApiTester $I
     */
    public function _before(\ApiTester $I)
    {
        $this->user = $I->createUser(['email' => 'user1@example.com', 'employeeId' => 'id789']);
        $I->grabService('security.token_storage')->setToken(new UsernamePasswordToken(
            $this->user,
            null,
            'main',
            $this->user->getRoles()
        ));
    }

    /**
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testMarkApproved(\ApiTester $I): void
    {
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createVacationWorkLog([
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createVacationWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/vacation_work_logs/bulk/mark_approved',
            ['workLogIds' => [$workLog->getId(), $workLog2->getId()]]
        );

        $I->canSeeEmailIsSent(1);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            ['id' => $workLog->getId()],
            ['id' => $workLog2->getId()],
        ]);
        $workLog = $I->grabEntityFromRepository(VacationWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $workLog2 = $I->grabEntityFromRepository(VacationWorkLog::class, [
            'id' => $workLog2->getId(),
        ]);
        $I->assertNotNull($workLog->getTimeApproved());
        $I->assertNull($workLog->getTimeRejected());
        $I->assertNotNull($workLog2->getTimeApproved());
        $I->assertNull($workLog2->getTimeRejected());
    }

    /**
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testAlreadyMarkedApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createVacationWorkLog([
            'timeApproved' => $time,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createVacationWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/vacation_work_logs/bulk/mark_approved',
            ['workLogIds' => [$workLog->getId(), $workLog2->getId()]]
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => sprintf('Vacation work log with id %d has been already approved.', $workLog->getId()),
        ]);
        $I->grabEntityFromRepository(VacationWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNotNull($workLog->getTimeApproved());
        $I->assertNull($workLog->getTimeRejected());
    }

    /**
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testAlreadyMarkedRejected(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createVacationWorkLog([
            'timeRejected' => $time,
            'workMonth' => $workMonth,
        ]);
        $workLog2 = $I->createVacationWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            '/vacation_work_logs/bulk/mark_approved',
            ['workLogIds' => [$workLog->getId(), $workLog2->getId()]]
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => sprintf('Vacation work log with id %d has been already rejected.', $workLog->getId()),
        ]);
        $I->grabEntityFromRepository(VacationWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
    }
}
