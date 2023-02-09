<?php

namespace api\User;

 use Symfony\Component\HttpFoundation\Response;

 class GetUserOptionsCest
 {
     /**
      * @var User|null
      */
     private $user;

     public function _before(\ApiTester $I)
     {
         $this->user = $I->createUser();
         $I->login($this->user);
     }

    public function testGetAll(\ApiTester $I)
    {
        $user1 = $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
            'employeeId' => 'id123',
        ]);
        $user2 = $I->createUser([
            'firstName' => 'Petr',
            'lastName' => 'Pavel',
            'email' => 'test2@visionapps.cz',
            'employeeId' => 'id456',
        ]);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET('/users_options');

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'email' => $this->user->getEmail(),
                'firstName' => $this->user->getFirstName(),
                'lastName' => $this->user->getLastName(),
            ],
            [
                'email' => $user1->getEmail(),
                'firstName' => $user1->getFirstName(),
                'lastName' => $user1->getLastName(),
            ],
            [
                'email' => $user2->getEmail(),
                'firstName' => $user2->getFirstName(),
                'lastName' => $user2->getLastName(),
            ],
        ]);
    }
 }
