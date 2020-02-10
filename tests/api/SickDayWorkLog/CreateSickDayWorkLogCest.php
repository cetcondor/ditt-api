<?php

namespace api\SickDayWorkLog;

use App\Entity\SickDayWorkLog;
use App\Entity\User;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreateSickDayWorkLogCest
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
    public function testCreateWithValidData(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/sick_day_work_logs.json', [
            'date' => $date->format(\DateTime::RFC3339),
            'variant' => 'WITH_NOTE',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'date' => $date->format(\DateTime::RFC3339),
            'variant' => 'WITH_NOTE',
        ]);
        $I->grabEntityFromRepository(SickDayWorkLog::class, [
            'date' => $date,
            'variant' => 'WITH_NOTE',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithValidSickChildData(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/sick_day_work_logs.json', [
            'childName' => 'Jan Novak',
            'childDateOfBirth' => $date->format(\DateTime::RFC3339),
            'date' => $date->format(\DateTime::RFC3339),
            'variant' => 'SICK_CHILD',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'childName' => 'Jan Novak',
            'childDateOfBirth' => $date->format(\DateTime::RFC3339),
            'date' => $date->format(\DateTime::RFC3339),
            'variant' => 'SICK_CHILD',
        ]);
        $I->grabEntityFromRepository(SickDayWorkLog::class, [
            'childName' => 'Jan Novak',
            'childDateOfBirth' => $date->format(\DateTime::RFC3339),
            'date' => $date,
            'variant' => 'SICK_CHILD',
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
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/sick_day_work_logs.json', [
            'date' => $date->format(\DateTime::RFC3339),
            'variant' => 'WITH_NOTE',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Cannot add or delete work log to closed work month.',
        ]);
        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(SickDayWorkLog::class, [
                'date' => $date,
                'variant' => 'WITH_NOTE',
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
        $I->sendPOST('/sick_day_work_logs.json', [
            'childName' => '',
            'childDateOfBirth' => null,
            'date' => null,
            'variant' => '',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'The data is either an empty string or null, you should pass a string '
            . 'that can be parsed with the passed format or a valid DateTime string.',
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(SickDayWorkLog::class, [
                'date' => $date,
                'variant' => '',
            ]);
        });
    }

    /**
     * @throws \Exception
     */
    public function testCreateWithInvalidSickDayData(\ApiTester $I): void
    {
        $date = new \DateTimeImmutable('2019-06-01T12:00:00');
        $I->createWorkMonth([
            'month' => $date->format('m'),
            'user' => $this->user,
            'year' => $I->getSupportedYear($date->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/sick_day_work_logs.json', [
            'childName' => '',
            'childDateOfBirth' => null,
            'date' => (new \DateTime('2019-06-01T12:00:00'))->format(\DateTime::RFC3339),
            'variant' => 'SICK_CHILD',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'Sick day work log required child`s name and date of birth if sick child is selected.',
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $date) {
            $I->grabEntityFromRepository(SickDayWorkLog::class, [
                'date' => $date,
                'variant' => 'SICK_CHILD',
            ]);
        });
    }
}
