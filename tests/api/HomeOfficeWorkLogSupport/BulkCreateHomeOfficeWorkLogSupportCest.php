<?php

namespace api\HomeOfficeWorkLogSupport;

use App\Entity\HomeOfficeWorkLogSupport;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class BulkCreateHomeOfficeWorkLogSupportCest
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
        $workLog = $I->createHomeOfficeWorkLog(['workMonth' => $workMonth]);
        $workLog2 = $I->createHomeOfficeWorkLog(['workMonth' => $workMonth]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/home_office_work_log_supports/bulk', [
            ['workLog' => sprintf('/home_office_work_logs/%s', $workLog->getId())],
            ['workLog' => sprintf('/home_office_work_logs/%s', $workLog2->getId())],
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
        $I->grabEntityFromRepository(HomeOfficeWorkLogSupport::class, [
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
        $I->grabEntityFromRepository(HomeOfficeWorkLogSupport::class, [
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
        $I->sendPOST('/home_office_work_log_supports/bulk', [
            ['workLog' => '/home_office_work_logs/999999'],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot denormalize work log support.',
        ]);
    }
}
