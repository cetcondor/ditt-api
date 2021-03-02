<?php

namespace api\User;

use Symfony\Component\HttpFoundation\Response;

class GetConfigCest
{
    public function testGet(\ApiTester $I)
    {
        $user = $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
        ]);
        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/configs');

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'supportedYears' => [
                ['year' => 2018],
                ['year' => 2019],
                ['year' => 2020],
            ],
            'workedHoursLimits' => [
                'lowerLimit' => [
                    'changeBy' => -900,
                    'limit' => 21600,
                ],
                'mediumLimit' => [
                    'changeBy' => -1800,
                    'limit' => 22500,
                ],
                'upperLimit' => [
                    'changeBy' => -2700,
                    'limit' => 34200,
                ],
            ],
        ]);
    }
}
