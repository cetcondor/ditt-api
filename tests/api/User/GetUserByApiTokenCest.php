<?php

namespace api\User;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class GetUserByApiTokenCest
{
    /**
     * @var User
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $this->user = $I->createUser(['apiToken' => 'token']);
        $I->login($this->user);
    }

    public function testGetUserByApiToken(\ApiTester $I)
    {
        $I->sendGET(sprintf('/users/api_token/%s', $this->user->getApiToken()));

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'firstName' => $this->user->getFirstName(),
            'lastName' => $this->user->getLastName(),
        ]);
    }
}
