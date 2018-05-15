<?php

namespace api\Config;

use Symfony\Component\HttpFoundation\Response;

class GetWorkLogCest
{
    public function testGetEmpty(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/work_logs.json');

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([]);
    }

    public function testGetAll(\ApiTester $I)
    {
        $startTime1 = (new \DateTimeImmutable());
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = (new \DateTimeImmutable());
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $user = $I->createUser();

        $I->createWorkLog([
            'startTime' => $startTime1,
            'endTime' => $endTime1,
            'user' => $user,
        ]);
        $I->createWorkLog([
            'startTime' => $startTime2,
            'endTime' => $endTime2,
            'user' => $user,
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/work_logs.json');

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'startTime' => $startTime1->format(\DateTime::RFC3339),
                'endTime' => $endTime1->format(\DateTime::RFC3339),
            ],
            [
                'startTime' => $startTime2->format(\DateTime::RFC3339),
                'endTime' => $endTime2->format(\DateTime::RFC3339),
            ],
        ]);
    }

    public function testGetFilter(\ApiTester $I)
    {
        $startTime1 = (new \DateTimeImmutable());
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = (new \DateTimeImmutable());
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $user = $I->createUser();

        $I->createWorkLog([
            'user' => $user,
            'startTime' => $startTime1,
            'endTime' => $endTime1,
        ]);
        $I->createWorkLog([
            'user' => $user,
            'startTime' => $startTime2,
            'endTime' => $endTime2,
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/work_logs.json?startTime[before]=%s', $startTime2->format(\DateTime::RFC3339)));

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'startTime' => $startTime1->format(\DateTime::RFC3339),
                'endTime' => $endTime1->format(\DateTime::RFC3339),
            ],
        ]);
    }
}
