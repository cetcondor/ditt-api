<?php

namespace api\User;

use Symfony\Component\HttpFoundation\Response;

class GetSupervisedUserCest
{
    public function testGetAll(\ApiTester $I)
    {
        $user = $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
            'employeeId' => 'id123',
        ]);
        $user2 = $I->createUser([
            'firstName' => 'Petr',
            'lastName' => 'Pavel',
            'email' => 'test2@visionapps.cz',
            'employeeId' => 'id456',
            'supervisor' => $user,
        ]);
        $user3 = $I->createUser([
            'firstName' => 'Marek',
            'lastName' => 'Lámal',
            'email' => 'test3@visionapps.cz',
            'employeeId' => 'id789',
            'supervisor' => $user,
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/users/%d/supervised_users', $user->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'email' => $user2->getEmail(),
                'firstName' => $user2->getFirstName(),
                'employeeId' => $user2->getEmployeeId(),
                'lastName' => $user2->getLastName(),
            ],
            [
                'email' => $user3->getEmail(),
                'employeeId' => $user3->getEmployeeId(),
                'firstName' => $user3->getFirstName(),
                'lastName' => $user3->getLastName(),
            ],
        ]);
        $I->cantSeeResponseContainsJson([
            [
                'email' => $user->getEmail(),
                'employeeId' => $user->getEmployeeId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ],
        ]);
    }

    public function testGetAllActive(\ApiTester $I)
    {
        $user = $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
            'employeeId' => 'id123',
        ]);
        $user2 = $I->createUser([
            'firstName' => 'Petr',
            'lastName' => 'Pavel',
            'email' => 'test2@visionapps.cz',
            'employeeId' => 'id456',
            'supervisor' => $user,
        ]);
        $user3 = $I->createUser([
            'firstName' => 'Marek',
            'lastName' => 'Lámal',
            'email' => 'test3@visionapps.cz',
            'employeeId' => 'id789',
            'supervisor' => $user,
            'isActive' => false,
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/users/%d/supervised_users?isActive=true', $user->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'email' => $user2->getEmail(),
                'employeeId' => $user2->getEmployeeId(),
                'firstName' => $user2->getFirstName(),
                'lastName' => $user2->getLastName(),
            ],
        ]);
        $I->cantSeeResponseContainsJson([
            [
                'email' => $user->getEmail(),
                'employeeId' => $user->getEmployeeId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ],
            [
                'email' => $user3->getEmail(),
                'employeeId' => $user3->getEmployeeId(),
                'firstName' => $user3->getFirstName(),
                'lastName' => $user3->getLastName(),
            ],
        ]);
    }
}
