<?php

namespace App\Controller;

use App\Entity\ParentalLeaveWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Repository\ParentalLeaveWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\UserRepository;
use App\Repository\WorkMonthRepository;
use App\Service\ParentalLeaveWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParentalLeaveWorkLogController extends AbstractController
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
     * @var ParentalLeaveWorkLogRepository
     */
    private $parentalLeaveWorkLogRepository;

    /**
     * @var ParentalLeaveWorkLogService
     */
    private $parentalLeaveWorkLogService;

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
        ParentalLeaveWorkLogRepository $parentalLeaveWorkLogRepository,
        ParentalLeaveWorkLogService $parentalLeaveWorkLogService,
        WorkMonthRepository $workMonthRepository,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->parentalLeaveWorkLogRepository = $parentalLeaveWorkLogRepository;
        $this->parentalLeaveWorkLogService = $parentalLeaveWorkLogService;
        $this->workMonthRepository = $workMonthRepository;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $parentalLeaveWorkLogs = [];

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

        foreach ($data['workLogs'] as $normalizedParentalLeaveWorkLog) {
            try {
                $parentalLeaveWorkLog = $this->denormalizer->denormalize(
                    $normalizedParentalLeaveWorkLog,
                    ParentalLeaveWorkLog::class
                );

                if (!$parentalLeaveWorkLog instanceof ParentalLeaveWorkLog) {
                    throw new NotNormalizableValueException();
                }

                $workMonth = $this->workMonthRepository->findByWorkLogAndUser($parentalLeaveWorkLog, $user);

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

            $parentalLeaveWorkLog->setWorkMonth($workMonth);
            $parentalLeaveWorkLogs[] = $parentalLeaveWorkLog;
        }

        foreach ($parentalLeaveWorkLogs as $index => $parentalLeaveWorkLog) {
            $errors = $this->validator->validate($parentalLeaveWorkLog);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Parental leave work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->parentalLeaveWorkLogService->createParentalLeaveWorkLogs($parentalLeaveWorkLogs);
        $normalizedParentalLeaveWorkLogs = [];

        foreach ($parentalLeaveWorkLogs as $parentalLeaveWorkLog) {
            $normalizedParentalLeaveWorkLogs[] = $this->normalizer->normalize(
                $parentalLeaveWorkLog,
                ParentalLeaveWorkLog::class,
                ['groups' => ['parental_leave_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedParentalLeaveWorkLogs, JsonResponse::HTTP_CREATED);
    }
}
