<?php

namespace api\WorkLog;

use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GetWorkMonthCest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param \ApiTester $I
     */
    public function _before(\ApiTester $I)
    {
        $prophet = new Prophet();
        $this->user = $I->createUser();
        $token = $prophet->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);
        $tokenStorage = $prophet->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());
        $I->getContainer()->set(TokenStorageInterface::class, $tokenStorage->reveal());
    }

    public function testGetEmpty(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/users/%d/work_months.json', $this->user->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([]);
    }

    public function testGetAll(\ApiTester $I)
    {
        $time = (new \DateTimeImmutable());

        $I->createWorkMonth([
            'month' => $time->format('m'),
            'user' => $this->user,
            'year' => $time->format('Y'),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/users/%d/work_months.json', $this->user->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'month' => intval($time->format('m')),
                'year' => intval($time->format('Y')),
            ],
        ]);
    }

    public function testGetDetail(\ApiTester $I)
    {
        $startTime1 = (new \DateTimeImmutable());
        $endTime1 = $startTime1->add(new \DateInterval('PT1M'));

        $startTime2 = (new \DateTimeImmutable());
        $endTime2 = $startTime1->add(new \DateInterval('PT1M'));

        $workMonth = $I->createWorkMonth([
            'month' => $startTime1->format('m'),
            'user' => $this->user,
            'year' => $startTime1->format('Y'),
        ]);

        $I->createWorkLog([
            'startTime' => $startTime1,
            'endTime' => $endTime1,
            'workMonth' => $workMonth,
        ]);
        $I->createWorkLog([
            'startTime' => $startTime2,
            'endTime' => $endTime2,
            'workMonth' => $workMonth,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/work_months/%d.json', $workMonth->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'id' => $workMonth->getId(),
            'month' => $workMonth->getMonth(),
            'workLogs' => [
//                [
//                    'startTime' => $startTime1->format(\DateTime::RFC3339),
//                    'endTime' => $endTime1->format(\DateTime::RFC3339),
//                ],
//                [
//                    'startTime' => $startTime2->format(\DateTime::RFC3339),
//                    'endTime' => $endTime2->format(\DateTime::RFC3339),
//                ],
            ],
            'year' => $workMonth->getYear(),
        ]);
    }
}
