<?php

namespace api\User;

use Symfony\Component\HttpFoundation\Response;

class GetUserCest
{
    public function testGetEmpty(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/users.json');

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([]);
    }

    public function testGetAll(\ApiTester $I)
    {
        $user = $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
        ]);
        $user2 = $I->createUser([
            'firstName' => 'Petr',
            'lastName' => 'Pavel',
            'email' => 'test2@visionapps.cz',
            'employeeId' => 'id456',
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/users.json');

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ],
            [
                'email' => $user2->getEmail(),
                'firstName' => $user2->getFirstName(),
                'lastName' => $user2->getLastName(),
            ],
        ]);
    }
}
