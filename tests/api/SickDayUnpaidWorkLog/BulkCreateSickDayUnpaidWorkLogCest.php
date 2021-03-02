<?php

namespace api\TimeOffWorkLog;

use App\Entity\SickDayUnpaidWorkLog;
use App\Entity\User;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class BulkCreateSickDayUnpaidWorkLogCest
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
        $I->sendPOST('/sick_day_unpaid_work_logs/bulk', [
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
        $I->grabEntityFromRepository(SickDayUnpaidWorkLog::class, [
            'date' => $date,
        ]);
        $I->grabEntityFromRepository(SickDayUnpaidWorkLog::class, [
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
        $I->sendPOST('/sick_day_unpaid_work_logs/bulk', [
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
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user2,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/sick_day_unpaid_work_logs/bulk', [
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

        $I->expectThrowable(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(SickDayUnpaidWorkLog::class, [
                'date' => $date,
            ]);
        });
    }
}
