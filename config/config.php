<?php

return [
    'supportedHolidays' => [
        new \DateTime('2018-08-08'),
        new \DateTime('2018-01-01'),
        new \DateTime('2018-12-24'),
        new \DateTime('2018-12-25'),
        new \DateTime('2018-12-26'),
        new \DateTime('2018-12-31'),
        new \DateTime('2019-01-01'),
        new \DateTime('2019-12-24'),
        new \DateTime('2019-12-25'),
        new \DateTime('2019-12-26'),
        new \DateTime('2019-12-31'),
        new \DateTime('2020-01-01'),
        new \DateTime('2020-12-24'),
        new \DateTime('2020-12-25'),
        new \DateTime('2020-12-26'),
        new \DateTime('2020-12-31'),
        new \DateTime('2021-01-01'),
        new \DateTime('2021-12-24'),
        new \DateTime('2021-12-25'),
        new \DateTime('2021-12-26'),
        new \DateTime('2021-12-31'),
    ],
    'supportedYear' => [
        2018,
        2019,
        2020,
        2021,
    ],
    'workedHoursLimits' => [
        'lowerLimit' => [
            'changeBy' => -1800,
            'limit' => 21600,
        ],
        'upperLimit' => [
            'changeBy' => -2700,
            'limit' => 32400,
        ],
    ]
];
