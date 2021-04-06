<?php

namespace api\OvertimeWorkLogSupport;

use App\Entity\OvertimeWorkLogSupport;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class BulkCreateOvertimeWorkLogSupportCest
{
    /**
     * @var User
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
    public function testCreateWithValidData(\ApiTester $I): void
    {
        $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => '123', 'supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createOvertimeWorkLog(['workMonth' => $workMonth]);
        $workLog2 = $I->createOvertimeWorkLog(['workMonth' => $workMonth]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/overtime_work_log_supports/bulk', [
            ['workLog' => sprintf('/overtime_work_logs/%s', $workLog->getId())],
            ['workLog' => sprintf('/overtime_work_logs/%s', $workLog2->getId())],
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            [
                'supportedBy' => ['id' => $this->user->getId()],
                'workLog' => ['id' => $workLog->getId()],
            ],
            [
                'supportedBy' => ['id' => $this->user->getId()],
                'workLog' => ['id' => $workLog2->getId()],
            ],
        ]);
        $I->grabEntityFromRepository(OvertimeWorkLogSupport::class, [
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
        $I->grabEntityFromRepository(OvertimeWorkLogSupport::class, [
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog2->getId()],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithInvalidData(\ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/overtime_work_log_supports/bulk', [
            ['workLog' => '/overtime_work_logs/999999'],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot denormalize work log support.',
        ]);
    }
}
