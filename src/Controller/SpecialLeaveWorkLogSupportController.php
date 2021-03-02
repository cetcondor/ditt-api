<?php

namespace App\Controller;

use App\Entity\SpecialLeaveWorkLogSupport;
use App\Entity\User;
use App\Service\SpecialLeaveWorkLogSupportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpecialLeaveWorkLogSupportController extends AbstractController
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
     * @var SpecialLeaveWorkLogSupportService
     */
    private $specialLeaveWorkLogSupportService;

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
        SpecialLeaveWorkLogSupportService $specialLeaveWorkLogSupportService,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->specialLeaveWorkLogSupportService = $specialLeaveWorkLogSupportService;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $specialLeaveWorkLogsSupport = [];

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

        foreach ($data as $normalizedSpecialLeaveWorkLogSupport) {
            try {
                $specialLeaveWorkLogSupport = $this->denormalizer->denormalize(
                    $normalizedSpecialLeaveWorkLogSupport,
                    SpecialLeaveWorkLogSupport::class
                );

                if (!$specialLeaveWorkLogSupport instanceof SpecialLeaveWorkLogSupport) {
                    throw new NotNormalizableValueException();
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log support.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $specialLeaveWorkLogSupport->setDateTime(new \DateTimeImmutable());
            $specialLeaveWorkLogSupport->setSupportedBy($token->getUser());

            $specialLeaveWorkLogsSupport[] = $specialLeaveWorkLogSupport;
        }

        foreach ($specialLeaveWorkLogsSupport as $index => $specialLeaveWorkLogSupport) {
            $errors = $this->validator->validate($specialLeaveWorkLogSupport);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Special leave work log support with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->specialLeaveWorkLogSupportService->createSpecialLeaveWorkLogSupport($specialLeaveWorkLogsSupport);
        $normalizedSpecialLeaveWorkLogsSupport = [];

        foreach ($specialLeaveWorkLogsSupport as $item) {
            $normalizedSpecialLeaveWorkLogSupport[] = $this->normalizer->normalize(
                $item,
                SpecialLeaveWorkLogSupport::class,
                ['groups' => ['specialLeave_work_log_support_out_detail']]
            );
        }

        return JsonResponse::create($normalizedSpecialLeaveWorkLogsSupport, JsonResponse::HTTP_CREATED);
    }
}
