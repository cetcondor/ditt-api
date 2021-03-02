<?php

namespace App\Controller;

use App\Entity\SickDayWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Repository\WorkMonthRepository;
use App\Service\SickDayWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SickDayWorkLogController extends AbstractController
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
     * @var SickDayWorkLogService
     */
    private $sickDayWorkLogService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        SickDayWorkLogService $sickDayWorkLogService,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        WorkMonthRepository $workMonthRepository
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->sickDayWorkLogService = $sickDayWorkLogService;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->workMonthRepository = $workMonthRepository;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $sickDayWorkLogs = [];

        if (!$data || !is_array($data)) {
            return JsonResponse::create(
                ['detail' => 'Expected array of work logs.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $token = $this->tokenStorage->getToken();

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$token || !$token->getUser() instanceof User) {
            return JsonResponse::create(
                ['detail' => 'Cannot create work log without user.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        foreach ($data as $normalizedSickDayWorkLog) {
            try {
                $sickDayWorkLog = $this->denormalizer->denormalize(
                    $normalizedSickDayWorkLog,
                    SickDayWorkLog::class
                );

                if (!$sickDayWorkLog instanceof SickDayWorkLog) {
                    throw new NotNormalizableValueException();
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workMonth = $this->workMonthRepository->findByWorkLogAndUser($sickDayWorkLog, $token->getUser());

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

            $sickDayWorkLog->setWorkMonth($workMonth);
            $sickDayWorkLogs[] = $sickDayWorkLog;
        }

        foreach ($sickDayWorkLogs as $index => $sickDayWorkLog) {
            $errors = $this->validator->validate($sickDayWorkLog);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Sick day work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->sickDayWorkLogService->createSickDayWorkLogs($sickDayWorkLogs);
        $normalizedSickDayWorkLogs = [];

        foreach ($sickDayWorkLogs as $sickDayWorkLog) {
            $normalizedSickDayWorkLogs[] = $this->normalizer->normalize(
                $sickDayWorkLog,
                SickDayWorkLog::class,
                ['groups' => ['sick_day_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedSickDayWorkLogs, JsonResponse::HTTP_CREATED);
    }
}
