<?php

namespace api\VacationWorkLogSupport;

use App\Entity\User;
use App\Entity\VacationWorkLogSupport;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreateVacationWorkLogSupportCest
{
    /**
     * @var User
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $prophet = new Prophet();
        $this->user = $I->createUser(['email' => 'user1@example.com', 'employeeId' => 'id789']);
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithValidData(\ApiTester $I): void
    {
        $user = $I->createUser(['supervisor' => $this->user]);
        $workMonth = $I->createWorkMonth(['user' => $user]);
        $workLog = $I->createVacationWorkLog(['workMonth' => $workMonth]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/vacation_work_log_supports.json', [
            'workLog' => sprintf('/vacation_work_logs/%s', $workLog->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'supportedBy' => ['id' => $this->user->getId()],
            'workLog' => ['id' => $workLog->getId()],
        ]);
        $I->grabEntityFromRepository(VacationWorkLogSupport::class, [
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
        $I->sendPOST('/vacation_work_log_supports.json', [
            'workLog' => '/vacation_work_logs/999999',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Item not found for "/vacation_work_logs/999999".',
        ]);
    }
}
