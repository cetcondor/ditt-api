<?php

namespace App\Service;

use App\Entity\Config;
use App\Repository\SupportedHolidayRepository;
use App\Repository\SupportedYearRepository;

class ConfigService
{
    /**
     * @var SupportedYearRepository
     */
    private $supportedYearRepository;

    /**
     * @var SupportedHolidayRepository
     */
    private $supportedHolidayRepository;

    /**
     * @param SupportedYearRepository $supportedYearRepository
     * @param SupportedHolidayRepository $supportedHolidayRepository
     */
    public function __construct(
        SupportedYearRepository $supportedYearRepository,
        SupportedHolidayRepository $supportedHolidayRepository
    ) {
        $this->supportedYearRepository = $supportedYearRepository;
        $this->supportedHolidayRepository = $supportedHolidayRepository;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        $config = new Config();

        $config->setSupportedYears($this->supportedYearRepository->getRepository()->findAll());
        $config->setSupportedHolidays($this->supportedHolidayRepository->getRepository()->findAll());

        return $config;
    }
}
