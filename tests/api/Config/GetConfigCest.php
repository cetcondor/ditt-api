<?php

namespace api\User;

use Symfony\Component\HttpFoundation\Response;

class GetConfigCest
{
    public function testGet(\ApiTester $I)
    {
        $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
        ]);
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
                    'changeBy' => -1800,
                    'limit' => 21600,
                ],
                'upperLimit' => [
                    'changeBy' => -2700,
                    'limit' => 32400,
                ],
            ],
        ]);
    }
}
