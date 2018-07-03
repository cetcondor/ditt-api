<?php

namespace api\User;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class NewPasswordCest
{
    public function testResetPasswordValidData(\ApiTester $I)
    {
        $user = $I->createUser(['resetPasswordToken' => 'token']);
        $password = $user->getPassword();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/new-password', [
            'newPlainPassword' => 'newPassword',
            'resetPasswordToken' => 'token',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);

        $savedUser = $I->grabEntityFromRepository(User::class, [
            'email' => $user->getEmail(),
        ]);

        $I->assertNotEquals($password, $savedUser->getPassword());
    }

    public function testResetPasswordInvalidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/new-password', [
            'newPlainPassword' => 'newPassword',
            'resetPasswordToken' => 'token',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'User with entered reset password token was not found.',
        ]);
    }
}
