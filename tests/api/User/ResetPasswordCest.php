<?php

namespace api\User;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordCest
{
    public function testResetPasswordValidData(\ApiTester $I)
    {
        $user = $I->createUser();

        $I->assertNull($user->getResetPasswordToken());

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/reset-password', [
            'email' => $user->getEmail(),
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);

        $I->seeEmailIsSent();

        $savedUser = $I->grabEntityFromRepository(User::class, [
            'email' => $user->getEmail(),
        ]);

        $I->assertNotNull($savedUser->getResetPasswordToken());
    }

    public function testResetPasswordInvalidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/reset-password', [
            'email' => 'unknown@example.com',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'User with email unknown@example.com was not found',
        ]);
    }
}
