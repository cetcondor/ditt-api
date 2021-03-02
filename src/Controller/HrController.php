<?php

namespace App\Controller;

use App\Entity\SickDayWorkLog;
use App\Entity\User;
use App\Entity\WorkHours;
use App\Repository\SickDayWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\UserRepository;
use App\Repository\WorkHoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HrController extends AbstractController
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var SickDayWorkLogRepository
     */
    private $sickDayWorkLogRepository;

    /**
     * @var SupportedYearRepository
     */
    private $supportedYearRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var WorkHoursRepository
     */
    private $workHoursRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        SickDayWorkLogRepository $sickDayWorkLogRepository,
        SupportedYearRepository $supportedYearRepository,
        UserRepository $userRepository,
        WorkHoursRepository $workHoursRepository
    ) {
        $this->normalizer = $normalizer;
        $this->sickDayWorkLogRepository = $sickDayWorkLogRepository;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->userRepository = $userRepository;
        $this->workHoursRepository = $workHoursRepository;
    }

    public function changesAndAbsenceRegistrations(Request $request): Response
    {
        $dateFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $request->query->get('dateFrom'));
        $dateTo = \DateTimeImmutable::createFromFormat('d.m.Y', $request->query->get('dateTo'));

        if ($dateFrom === false || $dateTo === false) {
            return JsonResponse::create(
                ['detail' => 'Expected dateFrom and dateTo parameters.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod(
            $dateFrom->modify('first day of this month'),
            $interval,
            $dateTo->modify('first day of next month')
        );

        $monthWithYearList = [];
        foreach ($period as $date) {
            $monthWithYearList[] = [
                'year' => $this->supportedYearRepository->getRepository()->find(intval($date->format('Y'))),
                'month' => intval($date->format('m')),
            ];
        }

        $normalizedData = [];
        foreach ($this->userRepository->getRepository()->findAll() as $user) {
            $workHours = [];
            foreach ($monthWithYearList as $monthYear) {
                $workHours[] = $this->workHoursRepository->findOne(
                    $monthYear['year'],
                    $monthYear['month'],
                    $user
                );
            }

            $sickDays = $this->sickDayWorkLogRepository->findAllCreatedByUserBetweenTwoDates($user, $dateFrom, $dateTo);

            $normalizedData[] = [
                'sickDays' => $this->normalizer->normalize(
                    $sickDays,
                    SickDayWorkLog::class,
                    ['groups' => ['hr_out_detail']]
                ),
                'user' => $this->normalizer->normalize(
                    $user,
                    User::class,
                    ['groups' => ['hr_out_detail']]
                ),
                'workHours' => $this->normalizer->normalize(
                    $workHours,
                    WorkHours::class,
                    ['groups' => ['hr_out_detail']]
                ),
            ];
        }

        return JsonResponse::create($normalizedData, JsonResponse::HTTP_OK);
    }

    public function yearOverview(Request $request): Response
    {
        $dateFrom = (new \DateTimeImmutable())->modify('-1 year');

        $normalizedData = [];
        foreach ($this->userRepository->getRepository()->findAll() as $user) {
            $sickDays = $this->sickDayWorkLogRepository->findAllByUserFromDate($user, $dateFrom);

            $normalizedData[] = [
                'sickDays' => $this->normalizer->normalize(
                    $sickDays,
                    SickDayWorkLog::class,
                    ['groups' => ['hr_out_detail']]
                ),
                'user' => $this->normalizer->normalize(
                    $user,
                    User::class,
                    ['groups' => ['hr_out_detail']]
                ),
            ];
        }

        return JsonResponse::create($normalizedData, JsonResponse::HTTP_OK);
    }
}
