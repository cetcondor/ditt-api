<?php

namespace api\TimeOffWorkLogSupport;

use App\Entity\TimeOffWorkLogSupport;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class CreateTimeOffWorkLogSupportCest
{
    /**
     * @var User
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $this->user = $I->createUser(['email' => 'user1@example.com', 'employeeId' => 'id789']);
        $I->login($this->user);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithValidData(\ApiTester $I): void
    {
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createTimeOffWorkLog(['workMonth' => $workMonth]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/time_off_work_log_supports.json', [
            'workLog' => sprintf('/time_off_work_logs/%s', $workLog->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
        $I->grabEntityFromRepository(TimeOffWorkLogSupport::class, [
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithInvalidData(\ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/time_off_work_log_supports.json', [
            'workLog' => '/time_off_work_logs/999999',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Item not found for "/time_off_work_logs/999999".',
        ]);
    }
}
