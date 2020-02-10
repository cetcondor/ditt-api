<?php

namespace api\BusinessTripWorkLog;

use App\Entity\BusinessTripWorkLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MarkBusinessTripWorkLogRejectedCest
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
    public function testMarkRejected(\ApiTester $I): void
    {
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createBusinessTripWorkLog([
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            sprintf('/business_trip_work_logs/%d/mark_rejected', $workLog->getId()),
            ['rejectionMessage' => 'Rejection message.']
        );

        $I->canSeeEmailIsSent();

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workLog->getId(),
        ]);
        $workLog = $I->grabEntityFromRepository(BusinessTripWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createBusinessTripWorkLog([
            'timeApproved' => $time,
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            sprintf('/business_trip_work_logs/%d/mark_rejected', $workLog->getId()),
            ['rejectionMessage' => 'Rejection message.']
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Business trip work log month has been already approved.',
        ]);
        $I->grabEntityFromRepository(BusinessTripWorkLog::class, [
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
        $workLog = $I->createBusinessTripWorkLog([
            'timeRejected' => $time,
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(
            sprintf('/business_trip_work_logs/%d/mark_rejected', $workLog->getId()),
            ['rejectionMessage' => 'Rejection message.']
        );

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Business trip work log month has been already rejected.',
        ]);
        $I->grabEntityFromRepository(BusinessTripWorkLog::class, [
            'id' => $workLog->getId(),
        ]);
        $I->assertNull($workLog->getTimeApproved());
        $I->assertNotNull($workLog->getTimeRejected());
    }
}
