<?php

namespace api\WorkLog;

use App\Entity\User;
use App\Entity\WorkLog;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreateWorkLogCest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param \ApiTester $I
     */
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
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testCreateWithValidData(\ApiTester $I): void
    {
        $startTime = (new \DateTimeImmutable());
        $endTime = $startTime->add(new \DateInterval('PT1M'));
        $I->createWorkMonth([
            'month' => $startTime->format('m'),
            'user' => $this->user,
            'year' => $I->getSupportedYear($startTime->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/work_logs.json', [
            'startTime' => $startTime->format(\DateTime::RFC3339),
            'endTime' => $endTime->format(\DateTime::RFC3339),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'startTime' => $startTime->format(\DateTime::RFC3339),
            'endTime' => $endTime->format(\DateTime::RFC3339),
        ]);
        $I->grabEntityFromRepository(WorkLog::class, [
            'startTime' => $startTime,
            'endTime' => $endTime,
        ]);
    }

    /**
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testCreateWithClosedMonth(\ApiTester $I): void
    {
        $startTime = (new \DateTimeImmutable());
        $endTime = $startTime->add(new \DateInterval('PT1M'));
        $I->createWorkMonth([
            'month' => $startTime->format('m'),
            'status' => 'APPROVED',
            'user' => $this->user,
            'year' => $I->getSupportedYear($startTime->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/work_logs.json', [
            'startTime' => $startTime->format(\DateTime::RFC3339),
            'endTime' => $endTime->format(\DateTime::RFC3339),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot add or delete work log to closed work month.',
        ]);
        $I->expectException(NoResultException::class, function () use ($I, $startTime, $endTime) {
            $I->grabEntityFromRepository(WorkLog::class, [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
        });
    }

    /**
     * @param \ApiTester $I
     * @throws \Exception
     */
    public function testCreateWithInvalidData(\ApiTester $I): void
    {
        $startTime = (new \DateTimeImmutable());
        $endTime = $startTime->add(new \DateInterval('PT1M'));
        $I->createWorkMonth([
            'month' => $startTime->format('m'),
            'user' => $this->user,
            'year' => $I->getSupportedYear($startTime->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/work_logs.json', [
            'startTime' => $endTime->format(\DateTime::RFC3339),
            'endTime' => $startTime->format(\DateTime::RFC3339),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $startTime, $endTime) {
            $I->grabEntityFromRepository(WorkLog::class, [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
        });
    }
}
