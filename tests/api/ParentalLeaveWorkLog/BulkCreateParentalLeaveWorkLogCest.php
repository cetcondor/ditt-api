<?php

namespace api\TimeOffWorkLog;

use App\Entity\ParentalLeaveWorkLog;
use App\Entity\User;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BulkCreateParentalLeaveWorkLogCest
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
    public function testBulkCreateWithValidData(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $date2 = $date->add(new \DateInterval('P1D'));
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/parental_leave_work_logs/bulk', [
            'user' => [
              'id' => $this->user2->getId(),
            ],
            'workLogs' => [
                ['date' => $date->format(\DateTime::RFC3339)],
                ['date' => $date2->format(\DateTime::RFC3339)],
            ],
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            ['date' => $date->format(\DateTime::RFC3339)],
            ['date' => $date2->format(\DateTime::RFC3339)],
        ]);
        $I->grabEntityFromRepository(ParentalLeaveWorkLog::class, [
            'date' => $date,
        ]);
        $I->grabEntityFromRepository(ParentalLeaveWorkLog::class, [
            'date' => $date2,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithClosedMonth(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'status' => 'APPROVED',
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/parental_leave_work_logs/bulk', [
            'user' => [
                'id' => $this->user2->getId(),
            ],
            'workLogs' => [
                ['date' => $date->format(\DateTime::RFC3339)],
            ],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot add work log to closed work month.',
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
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/parental_leave_work_logs/bulk', [
            'user' => [
                'id' => $this->user2->getId(),
            ],
            'workLogs' => [
                ['date' => null],
            ],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot denormalize work log.',
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(ParentalLeaveWorkLog::class, [
                'date' => $date,
            ]);
        });
    }
}
