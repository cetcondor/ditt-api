<?php

namespace api\Config;

use App\Entity\WorkLog;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class CreateWorkLogCest
{
    public function testCreateWithValidData(\ApiTester $I)
    {
        $startTime = (new \DateTimeImmutable());
        $endTime = $startTime->add(new \DateInterval('PT1M'));

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/work_logs.json', [
            'startTime' => $startTime->format(\DateTime::RFC3339),
            'endTime' => $endTime->format(\DateTime::RFC3339),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'startTime' => $startTime->format(\DateTime::RFC3339),
            'endTime' => $endTime->format(\DateTime::RFC3339),
        ]);
        $I->grabEntityFromRepository(WorkLog::class, [
            'startTime' => $startTime,
            'endTime' => $endTime,
        ]);
    }

    public function testCreateWithInvalidData(\ApiTester $I)
    {
        $startTime = (new \DateTimeImmutable());
        $endTime = $startTime->add(new \DateInterval('PT1M'));

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/work_logs.json', [
            'startTime' => $endTime->format(\DateTime::RFC3339),
            'endTime' => $startTime->format(\DateTime::RFC3339),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectException(NoResultException::class, function () use ($I, $startTime, $endTime) {
            $I->grabEntityFromRepository(WorkLog::class, [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
        });
    }
}
