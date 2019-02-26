<?php

namespace api\TimeOffWorkLog;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BulkCreateVacationWorkLogCest
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
        $this->user = $I->createUser(['vacationDays' => 2]);
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
     * @param \ApiTester $I
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
        $I->expectException(NoResultException::class, function () use ($I, $date2, $date3) {
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date2,
            ]);
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date3,
            ]);
        });
    }

    /**
     * @param \ApiTester $I
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
        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date,
            ]);
        });
    }

    /**
     * @param \ApiTester $I
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

        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(VacationWorkLog::class, [
                'date' => $date,
            ]);
        });
    }
}
