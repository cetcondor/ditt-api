<?php

namespace api\User;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ResetApiTokenCest
{
    public function testResetApiToken(\ApiTester $I)
    {
        $user = $I->createUser([
            'apiToken' => 'token',
        ]);

        $I->assertNotNull($user->getApiToken());

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/users/%d/api_token/reset', $user->getId()));
        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);

        $savedUser = $I->grabEntityFromRepository(User::class, [
            'id' => $user->getId(),
        ]);

        $I->assertNull($savedUser->getApiToken());
    }
}
