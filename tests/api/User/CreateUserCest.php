<?php

namespace api\User;

use App\Entity\User;
use App\Entity\UserYearStats;
use App\Entity\WorkMonth;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreateUserCest
{
    public function _before(\ApiTester $I)
    {
        $prophet = new Prophet();
        $user = $I->createUser();
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    public function testCreateWithValidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/users.json', [
            'email' => 'test@visionapps.cz',
            'employeeId' => '123',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => 'password',
            'workHours' => $I->generateWorkHours(100),
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
            'workHours' => $I->generateWorkHours(100),
        ]);
        $user = $I->grabEntityFromRepository(User::class, [
            'email' => 'test@visionapps.cz',
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $user) {
            $I->grabEntityFromRepository(WorkMonth::class, [
                'month' => 12,
                'user' => $user,
                'year' => 2017,
            ]);
        });
        $I->grabEntityFromRepository(WorkMonth::class, [
            'month' => 1,
            'user' => $user,
            'year' => 2018,
        ]);
        $I->grabEntityFromRepository(WorkMonth::class, [
            'month' => 12,
            'user' => $user,
            'year' => 2021,
        ]);
        $I->expectException(NoResultException::class, function () use ($I, $user) {
            $I->grabEntityFromRepository(WorkMonth::class, [
                'month' => 1,
                'user' => $user,
                'year' => 2022,
            ]);
        });

        $I->grabEntityFromRepository(UserYearStats::class, [
            'user' => $user,
            'year' => 2018,
        ]);
        $I->grabEntityFromRepository(UserYearStats::class, [
            'user' => $user,
            'year' => 2021,
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
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectException(NoResultException::class, function () use ($I) {
            $I->grabEntityFromRepository(User::class, [
                'email' => 'INVALID',
            ]);
        });
    }
}
