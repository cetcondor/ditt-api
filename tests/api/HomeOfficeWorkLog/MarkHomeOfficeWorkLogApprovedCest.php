<?php

namespace api\HomeOfficeWorkLog;

use App\Entity\HomeOfficeWorkLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MarkHomeOfficeWorkLogApprovedCest
{
    /**
     * @var User|null
     */
    private $user;

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
     * @throws \Exception
     */
    public function testMarkApproved(\ApiTester $I): void
    {
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createHomeOfficeWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/home_office_work_logs/%d/mark_approved', $workLog->getId()));

        $I->canSeeEmailIsSent();

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workLog->getId(),
        ]);
        $workLog = $I->grabEntityFromRepository(HomeOfficeWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNotNull($workLog->getTimeApproved());
        $I->assertNull($workLog->getTimeRejected());
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createHomeOfficeWorkLog([
            'timeApproved' => $time,
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/home_office_work_logs/%d/mark_approved', $workLog->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Home office work log month has been already approved.',
        ]);
        $I->grabEntityFromRepository(HomeOfficeWorkLog::class, [
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
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createHomeOfficeWorkLog([
            'timeRejected' => $time,
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/home_office_work_logs/%d/mark_approved', $workLog->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Home office work log month has been already rejected.',
        ]);
        $I->grabEntityFromRepository(HomeOfficeWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
    }
}
