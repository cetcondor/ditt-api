<?php

namespace api\User;

use App\Entity\User;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EditUserCest
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

    public function testEditWithValidData(\ApiTester $I)
    {
        $user = $I->createUser(['email' => 'user1@example.com', 'employeeId' => 'id123']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/users/%s.json', $user->getId()), [
            'email' => 'user2@example.com',
            'employeeId' => 'id123',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => 'password',
            'workHours' => $I->generateWorkHours(100),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'email' => 'user2@example.com',
            'employeeId' => 'id123',
            'firstName' => 'First',
            'id' => $user->getId(),
            'isActive' => true,
            'lastName' => 'lastName',
            'roles' => ['ROLE_EMPLOYEE'],
            'supervisor' => null,
            'vacationDays' => 20,
        ]);
        $I->grabEntityFromRepository(User::class, [
            'email' => 'user2@example.com',
        ]);
    }

    public function testEditWithInvalidData(\ApiTester $I)
    {
        $user = $I->createUser(['email' => 'user1@example.com', 'employeeId' => 'id123']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/users/%s.json', $user->getId()), [
            'email' => 'INVALID',
            'employeeId' => 'id123',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => null,
            'workHours' => $I->generateWorkHours(100),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);
    }
}
