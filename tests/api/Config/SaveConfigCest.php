<?php

namespace api\User;

use App\Entity\SupportedHoliday;
use App\Entity\SupportedYear;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class SaveConfigCest
{
    public function testSave(\ApiTester $I)
    {
        $user = $I->createUser([
            'firstName' => 'Jan',
            'lastName' => 'Svoboda',
            'email' => 'test1@visionapps.cz',
        ]);
        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/configs', [
            'supportedYears' => [
                ['year' => 2021],
            ],
            'supportedHolidays' => [
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2018',
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2019',
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2020',
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2021',
                ],
                [
                    'day' => 1,
                    'month' => 2,
                    'year' => '/supported_years/2021',
                ],
            ],
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'supportedYears' => [
                ['year' => 2018],
                ['year' => 2019],
                ['year' => 2020],
                ['year' => 2021],
            ],
            'supportedHolidays' => [
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => [
                        'year' => 2018,
                    ],
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => [
                        'year' => 2019,
                    ],
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => [
                        'year' => 2020,
                    ],
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => [
                        'year' => 2021,
                    ],
                ],
                [
                    'day' => 1,
                    'month' => 2,
                    'year' => [
                        'year' => 2021,
                    ],
                ],
            ],
            'workedHoursLimits' => [
                'lowerLimit' => [
                    'changeBy' => -900,
                    'limit' => 21600,
                ],
                'mediumLimit' => [
                    'changeBy' => -1800,
                    'limit' => 22500,
                ],
                'upperLimit' => [
                    'changeBy' => -2700,
                    'limit' => 34200,
                ],
            ],
        ]);
        $I->grabEntityFromRepository(SupportedYear::class, [
            'year' => 2021,
        ]);
        $I->grabEntityFromRepository(SupportedHoliday::class, [
            'day' => 1,
            'month' => 2,
            'year' => 2021,
        ]);
    }

    public function testInvalidSave(\ApiTester $I)
    {
        $user = $I->createUser();
        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/configs', [
            'supportedYears' => [
                ['year' => 2017],
            ],
            'supportedHolidays' => [
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2018',
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2019',
                ],
                [
                    'day' => 1,
                    'month' => 1,
                    'year' => '/supported_years/2020',
                ],
                [
                    'day' => 0,
                    'month' => 0,
                    'year' => '/supported_years/2021',
                ],
            ],
        ]);

        $I->seeHttpHeader('Content-Type', 'application/json');
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'detail' => 'One of supported year or supported holiday is not valid.',
        ]);
        $I->expectThrowable(NoResultException::class, function () use ($I) {
            $I->grabEntityFromRepository(SupportedYear::class, [
                'year' => 2017,
            ]);
        });
        $I->expectThrowable(NoResultException::class, function () use ($I) {
            $I->grabEntityFromRepository(SupportedHoliday::class, [
                'day' => 1,
                'month' => 2,
                'year' => 2021,
            ]);
        });
    }
}
