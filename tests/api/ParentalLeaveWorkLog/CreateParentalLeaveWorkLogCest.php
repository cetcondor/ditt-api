<?php

namespace api\TimeOffWorkLog;

use App\Entity\ParentalLeaveWorkLog;
use App\Entity\User;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreateParentalLeaveWorkLogCest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $user2;

    public function _before(\ApiTester $I)
    {
        $prophet = new Prophet();
        $this->user = $I->createUser();
        $this->user2 = $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => 'id123']);
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
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $workMonth = $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/parental_leave_work_logs.json', [
            'date' => $date->format(\DateTime::RFC3339),
            'workMonth' => sprintf('/work_months/%s', $workMonth->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'date' => $date->format(\DateTime::RFC3339),
        ]);
        $I->grabEntityFromRepository(ParentalLeaveWorkLog::class, [
            'date' => $date,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithClosedMonth(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $workMonth = $I->createWorkMonth([
            'month' => $date->format('m'),
            'status' => 'APPROVED',
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/parental_leave_work_logs.json', [
            'date' => $date->format(\DateTime::RFC3339),
            'workMonth' => sprintf('/work_months/%s', $workMonth->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot add or delete work log to closed work month.',
        ]);
        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(ParentalLeaveWorkLog::class, [
                'date' => $date,
            ]);
        });
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithInvalidData(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $workMonth = $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/parental_leave_work_logs.json', [
            'date' => null,
            'workMonth' => sprintf('/work_months/%s', $workMonth->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'The data is either an empty string or null, you should pass a string '
            . 'that can be parsed with the passed format or a valid DateTime string.',
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(ParentalLeaveWorkLog::class, [
                'date' => $date,
            ]);
        });
    }
}
