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
        ]);
        $user2 = $I->createUser([
            'firstName' => 'Petr',
            'lastName' => 'Pavel',
            'email' => 'test2@visionapps.cz',
            'supervisor' => $user,
        ]);
        $user3 = $I->createUser([
            'firstName' => 'Marek',
            'lastName' => 'Lámal',
            'email' => 'test3@visionapps.cz',
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
                'lastName' => $user2->getLastName(),
            ],
            [
                'email' => $user3->getEmail(),
                'firstName' => $user3->getFirstName(),
                'lastName' => $user3->getLastName(),
            ],
        ]);
        $I->cantSeeResponseContainsJson([
            [
                'email' => $user->getEmail(),
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
        ]);
        $user2 = $I->createUser([
            'firstName' => 'Petr',
            'lastName' => 'Pavel',
            'email' => 'test2@visionapps.cz',
            'supervisor' => $user,
        ]);
        $user3 = $I->createUser([
            'firstName' => 'Marek',
            'lastName' => 'Lámal',
            'email' => 'test3@visionapps.cz',
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
                'firstName' => $user2->getFirstName(),
                'lastName' => $user2->getLastName(),
            ],
        ]);
        $I->cantSeeResponseContainsJson([
            [
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ],
            [
                'email' => $user3->getEmail(),
                'firstName' => $user3->getFirstName(),
                'lastName' => $user3->getLastName(),
            ],
        ]);
    }
}
