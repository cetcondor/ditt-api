<?php

namespace api\User;

use App\Entity\Contract;
use App\Entity\User;
use App\Entity\UserYearStats;
use App\Entity\WorkMonth;
use App\Repository\ContractRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class CreateUserCest
{
    public function _before(\ApiTester $I)
    {
        $user = $I->createUser();
        $I->login($user);
    }

    public function testCreateWithValidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/users.json', [
            'contracts' => $I->generateContractsNormalized(8.5),
            'email' => 'test@visionapps.cz',
            'employeeId' => '123',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => 'password',
            'vacations' => $I->generateVacationsNormalizedUri(25, -5),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'email' => 'test@visionapps.cz',
            'employeeId' => '123',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'roles' => ['ROLE_EMPLOYEE'],
            'supervisor' => null,
            'vacations' => $I->generateVacationsNormalized(25, -5),
        ]);
        $user = $I->grabEntityFromRepository(User::class, [
            'email' => 'test@visionapps.cz',
        ]);

        $I->grabEntityFromRepository(Contract::class, [
            'user' => $user,
        ]);

        $I->expectThrowable(NoResultException::class, function () use ($I, $user) {
            $I->grabEntityFromRepository(WorkMonth::class, [
                'month' => 12,
                'user' => $user,
                'year' => $I->getSupportedYear(2017),
            ]);
        });
        $I->grabEntityFromRepository(WorkMonth::class, [
            'month' => 1,
            'user' => $user,
            'year' => $I->getSupportedYear(2018),
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'month' => 12,
            'user' => $user,
            'year' => $I->getSupportedYear(2020),
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'month' => 1,
            'user' => $user,
            'year' => $I->getSupportedYear(2021),
        ]);
        $I->expectThrowable(NoResultException::class, function () use ($I, $user) {
            $I->grabEntityFromRepository(WorkMonth::class, [
                'month' => 1,
                'user' => $user,
                'year' => $I->getSupportedYear(2017),
            ]);
        });

        $I->grabEntityFromRepository(UserYearStats::class, [
            'user' => $user,
            'year' => $I->getSupportedYear(2018),
        ]);
        $I->grabEntityFromRepository(UserYearStats::class, [
            'user' => $user,
            'year' => $I->getSupportedYear(2019),
        ]);
        $I->grabEntityFromRepository(UserYearStats::class, [
            'user' => $user,
            'year' => $I->getSupportedYear(2020),
        ]);
        $I->grabEntityFromRepository(UserYearStats::class, [
            'user' => $user,
            'year' => $I->getSupportedYear(2021),
        ]);
    }

    public function testCreateWithInvalidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/users.json', [
            'email' => 'INVALID',
            'employeeId' => '123',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => null,
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectThrowable(NoResultException::class, function () use ($I) {
            $I->grabEntityFromRepository(User::class, [
                'email' => 'INVALID',
            ]);
        });
    }
}
