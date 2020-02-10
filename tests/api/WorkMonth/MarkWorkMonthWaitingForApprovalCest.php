<?php

namespace api\WorkLog;

use App\Entity\User;
use App\Entity\WorkMonth;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MarkWorkMonthWaitingForApprovalCest
{
    /**
     * @var User
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $prophet = new Prophet();
        $this->user = $I->createUser();
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    /**
     * @throws \Exception
     */
    public function testMarkWaitingForApproval(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $workMonth = $I->createWorkMonth([
            'month' => $time->format('m'),
            'status' => 'OPENED',
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/work_months/%d/mark_waiting_for_approval', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'status' => 'WAITING_FOR_APPROVAL',
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'status' => 'WAITING_FOR_APPROVAL',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testAlreadyMarkedWaitingForApproval(\ApiTester $I): void
    {
        $time = (new \DateTimeImmutable());
        $workMonth = $I->createWorkMonth([
            'month' => $time->format('m'),
            'status' => 'WAITING_FOR_APPROVAL',
            'user' => $this->user,
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/work_months/%d/mark_waiting_for_approval', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Work month has been already sent for approval.',
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'id' => $workMonth->getId(),
            'status' => 'WAITING_FOR_APPROVAL',
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

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/work_months/%d/mark_waiting_for_approval', $workMonth->getId()));

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
