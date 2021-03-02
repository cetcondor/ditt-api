<?php

namespace api\User;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class EditUserCest
{
    public function _before(\ApiTester $I)
    {
        $user = $I->createUser();
        $I->login($user);
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
            'vacations' => $I->generateVacationsNormalized(25, -5),
            'workHours' => $I->generateWorkHoursNormalized(30600),
        ]);

        // $I->seeEmailIsSent(2);

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
            'vacations' => $I->generateVacationsNormalized(25, -5),
            'workHours' => $I->generateWorkHoursNormalized(30600),
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
            'workHours' => $I->generateWorkHoursNormalized(100),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);
    }
}
