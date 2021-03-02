<?php

namespace api\WorkLog;

use Symfony\Component\HttpFoundation\Response;

class GetWorkHoursCest
{
    public function _before(\ApiTester $I)
    {
        $user = $I->createUser();
        $I->login($user);
    }

    public function testGetEmpty(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/work_hours.json');

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([]);
    }

    public function testGetAll(\ApiTester $I)
    {
        $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
            'employeeId' => 'id123',
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/work_hours.json');

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson($I->generateWorkHoursNormalized(21600));
    }
}
