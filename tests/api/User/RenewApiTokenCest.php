<?php

namespace api\User;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class RenewApiTokenCest
{
    public function testResetApiToken(\ApiTester $I)
    {
        $user = $I->createUser();
        $I->login($user);

        $I->assertNull($user->getApiToken());

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/users/%d/api_token/renew', $user->getId()));
        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);

        $savedUser = $I->grabEntityFromRepository(User::class, [
            'id' => $user->getId(),
        ]);

        $I->assertNotNull($savedUser->getApiToken());
    }
}
