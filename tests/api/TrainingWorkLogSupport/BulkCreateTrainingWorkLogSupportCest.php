<?php

namespace api\TrainingWorkLogSupport;

use App\Entity\TrainingWorkLogSupport;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class BulkCreateTrainingWorkLogSupportCest
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
        $workLog = $I->createTrainingWorkLog(['workMonth' => $workMonth]);
        $workLog2 = $I->createTrainingWorkLog(['workMonth' => $workMonth]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/training_work_log_supports/bulk', [
            ['workLog' => sprintf('/training_work_logs/%s', $workLog->getId())],
            ['workLog' => sprintf('/training_work_logs/%s', $workLog2->getId())],
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
        $I->grabEntityFromRepository(TrainingWorkLogSupport::class, [
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
        $I->grabEntityFromRepository(TrainingWorkLogSupport::class, [
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
        $I->sendPOST('/training_work_log_supports/bulk', [
            ['workLog' => '/training_work_logs/999999'],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot denormalize work log support.',
        ]);
    }
}
