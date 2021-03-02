<?php

namespace api\TimeOffWorkLog;

use App\Entity\SickDayUnpaidWorkLog;
use App\Entity\User;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class CreateSickDayUnpaidWorkLogCest
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
        $this->user = $I->createUser();
        $this->user2 = $user = $I->createUser(['email' => 'user2@example.com', 'employeeId' => 'id123']);
        $I->login($this->user);
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
        $I->sendPOST('/sick_day_unpaid_work_logs.json', [
            'date' => $date->format(\DateTime::RFC3339),
            'workMonth' => sprintf('/work_months/%s', $workMonth->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'date' => $date->format(\DateTime::RFC3339),
        ]);
        $I->grabEntityFromRepository(SickDayUnpaidWorkLog::class, [
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
        $I->sendPOST('/sick_day_unpaid_work_logs.json', [
            'date' => $date->format(\DateTime::RFC3339),
            'workMonth' => sprintf('/work_months/%s', $workMonth->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot add or delete work log to closed work month.',
        ]);
        $I->expectThrowable(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(SickDayUnpaidWorkLog::class, [
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
        $I->sendPOST('/sick_day_unpaid_work_logs.json', [
            'date' => null,
            'workMonth' => sprintf('/work_months/%s', $workMonth->getId()),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'The data is either an empty string or null, you should pass a string '
            . 'that can be parsed with the passed format or a valid DateTime string.',
        ]);

        $I->expectThrowable(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(SickDayUnpaidWorkLog::class, [
                'date' => $date,
            ]);
        });
    }
}
