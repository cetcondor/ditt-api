<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\VacationWorkLogSupport;
use App\Service\VacationWorkLogSupportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VacationWorkLogSupportController extends AbstractController
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
     * @var VacationWorkLogSupportService
     */
    private $vacationWorkLogSupportService;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        VacationWorkLogSupportService $vacationWorkLogSupportService,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->vacationWorkLogSupportService = $vacationWorkLogSupportService;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $vacationWorkLogsSupport = [];

        if (!$data || !is_array($data)) {
            return JsonResponse::create(
                ['detail' => 'Expected array of work log support.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $token = $this->tokenStorage->getToken();

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$token || !$token->getUser() instanceof User) {
            return JsonResponse::create(
                ['detail' => 'Cannot create work log support without user.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        foreach ($data as $normalizedVacationWorkLogSupport) {
            try {
                $vacationWorkLogSupport = $this->denormalizer->denormalize(
                    $normalizedVacationWorkLogSupport,
                    VacationWorkLogSupport::class
                );

                if (!$vacationWorkLogSupport instanceof VacationWorkLogSupport) {
                    throw new NotNormalizableValueException();
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log support.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $vacationWorkLogSupport->setDateTime(new \DateTimeImmutable());
            $vacationWorkLogSupport->setSupportedBy($token->getUser());

            $vacationWorkLogsSupport[] = $vacationWorkLogSupport;
        }

        foreach ($vacationWorkLogsSupport as $index => $vacationWorkLogSupport) {
            $errors = $this->validator->validate($vacationWorkLogSupport);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Vacation work log support with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->vacationWorkLogSupportService->createVacationWorkLogSupport($vacationWorkLogsSupport);
        $normalizedVacationWorkLogsSupport = [];

        foreach ($vacationWorkLogsSupport as $item) {
            $normalizedVacationWorkLogSupport[] = $this->normalizer->normalize(
                $item,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_support_out_detail']]
            );
        }

        return JsonResponse::create($normalizedVacationWorkLogsSupport, JsonResponse::HTTP_CREATED);
    }
}
