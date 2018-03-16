<?php

namespace api\Config;

use App\Entity\Config;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class CreateConfigCest
{
    public function testCreateWithValidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/configs.json', [
            'title' => 'Config title',
            'description' => 'Config description',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'title' => 'Config title',
            'description' => 'Config description',
        ]);
        $I->grabEntityFromRepository(Config::class, [
            'title' => 'Config title',
        ]);
    }

    public function testCreateWithInvalidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/configs.json', [
            'title' => 'invalid-data',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectException(NoResultException::class, function () use ($I) {
            $I->grabEntityFromRepository(Config::class, [
                'title' => 'invalid-data',
            ]);
        });
    }
}
