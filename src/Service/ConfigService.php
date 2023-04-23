<?php

namespace App\Service;

use App\Entity\Config;
use App\Entity\SupportedHoliday;
use App\Entity\SupportedYear;
use App\Entity\UserYearStats;
use App\Entity\Vacation;
use App\Entity\WorkMonth;
use App\Repository\SupportedHolidayRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfigService
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var SupportedYearRepository
     */
    private $supportedYearRepository;

    /**
     * @var SupportedHolidayRepository
     */
    private $supportedHolidayRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        DenormalizerInterface $denormalizer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SupportedYearRepository $supportedYearRepository,
        SupportedHolidayRepository $supportedHolidayRepository,
        UserRepository $userRepository
    ) {
        $this->denormalizer = $denormalizer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->supportedHolidayRepository = $supportedHolidayRepository;
        $this->userRepository = $userRepository;
    }

    public function getConfig(): Config
    {
        $config = new Config();

        $config->setSupportedYears($this->supportedYearRepository->findAll());
        $config->setSupportedHolidays($this->supportedHolidayRepository->findAll());

        return $config;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException|\InvalidArgumentException
     */
    public function saveConfig(array $supportedYearsNormalized, array $supportedHolidaysNormalized): Config
    {
        $supportedYears = [];
        $supportedHolidays = [];

        $newSupportedYears = [];

        $supportedYearRepository = $this->supportedYearRepository->getRepository();

        // Process supported years
        $this->entityManager->transactional(
            function (EntityManagerInterface $entityManager) use (
                &$supportedYears,
                $supportedYearsNormalized,
                $supportedYearRepository,
                &$newSupportedYears
            ) {
                // Denormalize supported years
                foreach ($supportedYearsNormalized as $supportedYearNormalized) {
                    $supportedYear = $this->denormalizer->denormalize(
                        $supportedYearNormalized,
                        SupportedYear::class
                    );

                    $errors = $this->validator->validate($supportedYear);

                    if (count($errors) > 0 || !$supportedYear instanceof SupportedYear) {
                        throw new \InvalidArgumentException('One of supported year is not valid.');
                    }

                    $supportedYears[] = $supportedYear;
                }

                // Persist new supported years
                foreach ($supportedYears as $newSupportedYear) {
                    if (!$supportedYearRepository->find($newSupportedYear)) {
                        $entityManager->persist($newSupportedYear);
                        $newSupportedYears[] = $newSupportedYear;
                    }
                }
            }
        );

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        // Process supported holidays
        try {
            // Denormalize supported holidays
            foreach ($supportedHolidaysNormalized as $supportedHolidayNormalized) {
                $supportedHoliday = $this->denormalizer->denormalize(
                    $supportedHolidayNormalized,
                    SupportedHoliday::class
                );

                $errors = $this->validator->validate($supportedHoliday);

                if (count($errors) > 0 || !$supportedHoliday instanceof SupportedHoliday) {
                    throw new \InvalidArgumentException('One of supported holiday is not valid.');
                }

                $supportedHolidays[] = $supportedHoliday;
            }

            // Remove all existing holidays
            $platform = $connection->getDatabasePlatform();
            $connection->executeQuery($platform->getTruncateTableSQL('supported_holiday'));

            // Persist new supported holidays
            foreach ($supportedHolidays as $newSupportedHoliday) {
                $this->entityManager->persist($newSupportedHoliday);
            }

            // Generate and persist all new dependent entities
            foreach ($newSupportedYears as $newSupportedYear) {
                foreach ($this->generateNewEntitiesWithYear($newSupportedYear) as $newEntity) {
                    $this->entityManager->persist($newEntity);
                }
            }

            $this->entityManager->flush();
            $connection->commit();
        } catch (\InvalidArgumentException $exception) {
            $connection->rollback();

            // Remove newly added supported years if persisting of supported holidays fails
            if (count($newSupportedYears) > 0) {
                foreach ($newSupportedYears as $newSupportedYear) {
                    $this->entityManager->remove($newSupportedYear);
                }

                $this->entityManager->flush();
            }

            throw $exception;
        }

        return $this->getConfig();
    }

    private function generateNewEntitiesWithYear(SupportedYear $supportedYear): array
    {
        $users = $this->userRepository->getRepository()->findAll();
        $newEntities = [];

        foreach ($users as $user) {
            $newEntities[] = (new UserYearStats())
                ->setYear($supportedYear)
                ->setUser($user);

            $newEntities[] = (new Vacation())
                ->setUser($user)
                ->setVacationDays(0)
                ->setVacationDaysCorrection(0)
                ->setYear($supportedYear);

            for ($month = 1; $month <= 12; ++$month) {
                $newEntities[] = (new WorkMonth())
                    ->setYear($supportedYear)
                    ->setMonth($month)
                    ->setUser($user);
            }
        }

        return $newEntities;
    }
}
