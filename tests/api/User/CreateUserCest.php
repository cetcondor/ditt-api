<?php

namespace api\User;

use App\Entity\User;
use Doctrine\ORM\NoResultException;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreateUserCest
{
    public function _before(\ApiTester $I)
    {
        $prophet = new Prophet();
        $user = $I->createUser();
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    public function testCreateWithValidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/users.json', [
            'email' => 'test@visionapps.cz',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => 'password',
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseContainsJson([
            'email' => 'test@visionapps.cz',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'roles' => ['ROLE_EMPLOYEE'],
            'supervisor' => null,
        ]);
        $I->grabEntityFromRepository(User::class, [
            'email' => 'test@visionapps.cz',
        ]);
    }

    public function testCreateWithInvalidData(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/users.json', [
            'email' => 'test@visionapps.cz',
            'firstName' => 'First',
            'isActive' => true,
            'lastName' => 'lastName',
            'plainPassword' => null,
        ]);

        $I->seeHttpHeader('Content-Type', 'application/problem+json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'violations' => [[]],
        ]);

        $I->expectException(NoResultException::class, function () use ($I) {
            $I->grabEntityFromRepository(User::class, [
                'email' => 'test@visionapps.cz',
            ]);
        });
    }
}
