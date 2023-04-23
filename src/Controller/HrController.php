<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\SickDayWorkLog;
use App\Entity\User;
use App\Repository\ContractRepository;
use App\Repository\SickDayWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\UserRepository;
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
     * @var ContractRepository
     */
    private $contractRepository;

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

    public function __construct(
        NormalizerInterface $normalizer,
        ContractRepository $contractRepository,
        SickDayWorkLogRepository $sickDayWorkLogRepository,
        SupportedYearRepository $supportedYearRepository,
        UserRepository $userRepository
    ) {
        $this->normalizer = $normalizer;
        $this->contractRepository = $contractRepository;
        $this->sickDayWorkLogRepository = $sickDayWorkLogRepository;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->userRepository = $userRepository;
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
            $sickDays = $this->sickDayWorkLogRepository->findAllCreatedByUserBetweenTwoDates($user, $dateFrom, $dateTo);
            $contracts = $this->contractRepository->findContractsBetweenDates($user, $dateFrom, $dateTo);

            $normalizedData[] = [
                'contracts' => $this->normalizer->normalize(
                    $contracts,
                    Contract::class,
                    ['groups' => ['hr_out_detail']]
                ),
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
