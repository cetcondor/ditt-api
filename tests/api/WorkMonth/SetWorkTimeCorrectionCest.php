<?php

namespace api\WorkMonth;

use App\Entity\User;
use App\Entity\WorkMonth;
use Symfony\Component\HttpFoundation\Response;

class SetWorkTimeCorrectionCest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var User|null
     */
    private $supervisor;

    public function _before(\ApiTester $I)
    {
        $this->supervisor = $I->createUser();
        $this->user = $I->createUser([
            'employeeId' => '123',
            'email' => 'user2@example.com',
            'supervisor' => $this->supervisor,
        ]);
        $I->login($this->supervisor);
    }

    /**
     * @throws \Exception
     */
    public function testSetWorkTimeCorrection(\ApiTester $I): void
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
        $I->sendPUT(
            sprintf('/work_months/%d/set_work_time_correction', $workMonth->getId()),
            ['workTimeCorrection' => 3600],
        );

        // $I->seeEmailIsSent();

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'workTimeCorrection' => 3600,
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'workTimeCorrection' => 3600,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testWithWorkMonthApproved(\ApiTester $I): void
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
        $I->sendPUT(
            sprintf('/work_months/%d/set_work_time_correction', $workMonth->getId()),
            ['workTimeCorrection' => 3600],
        );

        // $I->dontSeeEmailIsSent();

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot set work time correction to closed work month.',
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'workTimeCorrection' => 0,
        ]);
    }
}
