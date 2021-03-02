<?php

namespace api\WorkLog;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class GetWorkMonthByUserCest
{
    /**
     * @var User
     */
    private $user;

    public function _before(\ApiTester $I)
    {
        $this->user = $I->createUser();
        $I->login($this->user);
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
            'year' => $I->getSupportedYear($time->format('Y')),
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/users/%d/work_months.json', $this->user->getId()));

        $I->seeHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            [
                'month' => intval($time->format('m')),
                'year' => [
                    'year' => $time->format('Y'),
                ],
            ],
        ]);
    }
}
