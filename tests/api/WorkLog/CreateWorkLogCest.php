<?php

namespace api\WorkLog;

use App\Entity\User;
use App\Entity\WorkLog;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class CreateWorkLogCest
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
        $I->expectThrowable(NoResultException::class, function () use ($I, $startTime, $endTime) {
            $I->grabEntityFromRepository(WorkLog::class, [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
        });
    }

    /**
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
        $I->seeResponseCodeIs(Response::HTTP_UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectThrowable(NoResultException::class, function () use ($I, $startTime, $endTime) {
            $I->grabEntityFromRepository(WorkLog::class, [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
        });
    }
}
