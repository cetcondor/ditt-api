<?php

namespace App\Controller;

use App\Entity\SickDayUnpaidWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Repository\SickDayUnpaidWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\UserRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SickDayUnpaidWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SickDayUnpaidWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var SupportedYearRepository
     */
    private $supportedYearRepository;

    /**
     * @var SickDayUnpaidWorkLogRepository
     */
    private $sickDayUnpaidWorkLogRepository;

    /**
     * @var SickDayUnpaidWorkLogService
     */
    private $sickDayUnpaidWorkLogService;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        SupportedYearRepository $supportedYearRepository,
        SickDayUnpaidWorkLogRepository $sickDayUnpaidWorkLogRepository,
        SickDayUnpaidWorkLogService $sickDayUnpaidWorkLogService,
        WorkMonthRepository $workMonthRepository,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->sickDayUnpaidWorkLogRepository = $sickDayUnpaidWorkLogRepository;
        $this->sickDayUnpaidWorkLogService = $sickDayUnpaidWorkLogService;
        $this->workMonthRepository = $workMonthRepository;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $sickDayUnpaidWorkLogs = [];

        if (!$data || !is_array($data)) {
            return JsonResponse::create(
                ['detail' => 'Expected object with array of work logs and user.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if (!$data['workLogs'] || !is_array($data['workLogs'])) {
            return JsonResponse::create(
                ['detail' => 'Expected array of work logs.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if (!$data['user'] || !is_array($data['user']) || !$data['user']['id']) {
            return JsonResponse::create(
                ['detail' => 'Expected user.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $user = $this->userRepository->getRepository()->find($data['user']['id']);

            if (!$user instanceof User) {
                throw new NotNormalizableValueException();
            }
        } catch (NotNormalizableValueException $e) {
            return JsonResponse::create(
                ['detail' => 'Cannot denormalize user.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        foreach ($data['workLogs'] as $normalizedSickDayUnpaidWorkLog) {
            try {
                $sickDayUnpaidWorkLog = $this->denormalizer->denormalize(
                    $normalizedSickDayUnpaidWorkLog,
                    SickDayUnpaidWorkLog::class
                );

                if (!$sickDayUnpaidWorkLog instanceof SickDayUnpaidWorkLog) {
                    throw new NotNormalizableValueException();
                }

                $workMonth = $this->workMonthRepository->findByWorkLogAndUser($sickDayUnpaidWorkLog, $user);

                if (!$workMonth) {
                    return JsonResponse::create(
                        ['detail' => 'Cannot create work log without work month.'], JsonResponse::HTTP_BAD_REQUEST
                    );
                }

                if ($workMonth->getStatus() === WorkMonth::STATUS_APPROVED) {
                    return JsonResponse::create(
                        ['detail' => 'Cannot add work log to closed work month.'], JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $sickDayUnpaidWorkLog->setWorkMonth($workMonth);
            $sickDayUnpaidWorkLogs[] = $sickDayUnpaidWorkLog;
        }

        foreach ($sickDayUnpaidWorkLogs as $index => $sickDayUnpaidWorkLog) {
            $errors = $this->validator->validate($sickDayUnpaidWorkLog);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Sick day unpaid work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->sickDayUnpaidWorkLogService->createSickDayUnpaidWorkLogs($sickDayUnpaidWorkLogs);
        $normalizedSickDayUnpaidWorkLogs = [];

        foreach ($sickDayUnpaidWorkLogs as $sickDayUnpaidWorkLog) {
            $normalizedSickDayUnpaidWorkLogs[] = $this->normalizer->normalize(
                $sickDayUnpaidWorkLog,
                SickDayUnpaidWorkLog::class,
                ['groups' => ['sick_day_unpaid_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedSickDayUnpaidWorkLogs, JsonResponse::HTTP_CREATED);
    }
}
