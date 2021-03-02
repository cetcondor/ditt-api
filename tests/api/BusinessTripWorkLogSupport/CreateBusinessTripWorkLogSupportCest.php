<?php

namespace api\BusinessTripWorkLogSupport;

use App\Entity\BusinessTripWorkLogSupport;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class CreateBusinessTripWorkLogSupportCest
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
        $workLog = $I->createBusinessTripWorkLog(['workMonth' => $workMonth]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/business_trip_work_log_supports.json', [
            'workLog' => sprintf('/business_trip_work_logs/%s', $workLog->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
        $I->grabEntityFromRepository(BusinessTripWorkLogSupport::class, [
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
        $I->sendPOST('/business_trip_work_log_supports.json', [
            'workLog' => '/business_trip_work_logs/999999',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Item not found for "/business_trip_work_logs/999999".',
        ]);
    }
}
