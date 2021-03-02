<?php

namespace api\WorkLog;

use App\Entity\User;
use App\Entity\WorkMonth;
use Symfony\Component\HttpFoundation\Response;

class MarkWorkMonthApprovedCest
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
    public function testMarkApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $workMonth = $I->createWorkMonth([
            'month' => $time->format('m'),
            'status' => 'WAITING_FOR_APPROVAL',
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);
        $I->createUserYearStats([
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/work_months/%d/mark_approved', $workMonth->getId()));

        // $I->seeEmailIsSent();

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'status' => 'APPROVED',
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'status' => 'APPROVED',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testMarkedOpened(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $workMonth = $I->createWorkMonth([
            'month' => $time->format('m'),
            'status' => 'OPENED',
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);
        $I->createUserYearStats([
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/work_months/%d/mark_approved', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Work month has not been sent for approval yet.',
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'status' => 'OPENED',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedApproved(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $workMonth = $I->createWorkMonth([
            'month' => $time->format('m'),
            'status' => 'APPROVED',
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);
        $I->createUserYearStats([
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/work_months/%d/mark_approved', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Work month has been already approved.',
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'status' => 'APPROVED',
        ]);
    }
}
