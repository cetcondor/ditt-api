<?php

namespace api\TimeOffWorkLog;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class BulkCreateVacationWorkLogCest
{
    /**
     * @var User
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $this->user = $I->createUser([
            'vacations' => function () use ($I) {
                $vacations = [];

                foreach ($I->generateVacations(3, -1) as $generatedVacation) {
                    $vacations[] = (new \App\Entity\Vacation())
                        ->setYear($generatedVacation['year'])
                        ->setVacationDays($generatedVacation['vacationDays'])
                        ->setVacationDaysCorrection($generatedVacation['vacationDaysCorrection']);
                }

                return $vacations;
            },
        ]);
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
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/vacation_work_logs/bulk', [
            ['date' => $date->format(\DateTime::RFC3339)],
            ['date' => $date2->format(\DateTime::RFC3339)],
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            ['date' => $date->format(\DateTime::RFC3339)],
            ['date' => $date2->format(\DateTime::RFC3339)],
        ]);
        $I->grabEntityFromRepository(VacationWorkLog::class, [
            'date' => $date,
        ]);
        $I->grabEntityFromRepository(VacationWorkLog::class, [
            'date' => $date2,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithExhaustedVacationDays(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $date2 = $date->add(new \DateInterval('P1D'));
        $date3 = $date2->add(new \DateInterval('P1D'));

        $workMonth = $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);
        $I->createVacationWorkLog([
            'date' => $date,
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/vacation_work_logs/bulk', [
            ['date' => $date2->format(\DateTime::RFC3339)],
            ['date' => $date3->format(\DateTime::RFC3339)],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Set duration exceeds number of vacation days allocated for this year.',
        ]);
        $I->expectThrowable(NoResultException::class, function () use ($I, $date2, $date3) {
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date2,
            ]);
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date3,
            ]);
        });
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
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/vacation_work_logs/bulk', [
            ['date' => $date->format(\DateTime::RFC3339)],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot add work log to closed work month.',
        ]);
        $I->expectThrowable(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(VacationWorkLog::class, [
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
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/vacation_work_logs/bulk', [
            ['date' => null],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot denormalize work log.',
        ]);

        $I->expectThrowable(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date,
            ]);
        });
    }
}
