<?php

namespace App\Controller;

use App\Entity\SpecialWorkLogSupportInterface;
use App\Entity\User;
use App\Service\SpecialWorkLogSupportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractSpecialWorkLogSupportController extends AbstractController
{
    protected DenormalizerInterface $denormalizer;
    protected NormalizerInterface $normalizer;
    protected SpecialWorkLogSupportService $specialWorkLogSupportService;
    protected ValidatorInterface $validator;

    protected string $entityClassName;
    protected string $entityNormalizationGroup;

    public function __construct(
        DenormalizerInterface $denormalizer,
        NormalizerInterface $normalizer,
        SpecialWorkLogSupportService $specialWorkLogSupportService,
        ValidatorInterface $validator,
        string $entityClassName,
        string $entityNormalizationGroup
    ) {
        $this->denormalizer = $denormalizer;
        $this->normalizer = $normalizer;
        $this->specialWorkLogSupportService = $specialWorkLogSupportService;
        $this->validator = $validator;

        $this->entityClassName = $entityClassName;
        $this->entityNormalizationGroup = $entityNormalizationGroup;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!$data || !is_array($data)) {
            return new JsonResponse(
                ['detail' => 'Expected array of work log support.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$this->getUser() || !$this->getUser() instanceof User) {
            return new JsonResponse(
                ['detail' => 'Cannot create work log support without user.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogsSupports = [];

        foreach ($data as $normalizedWorkLogSupport) {
            try {
                $workLogSupport = $this->denormalizer->denormalize(
                    $normalizedWorkLogSupport,
                    $this->entityClassName
                );

                if (!$workLogSupport instanceof SpecialWorkLogSupportInterface) {
                    throw new NotNormalizableValueException();
                }
            } catch (\Exception $e) {
                return new JsonResponse(
                    ['detail' => 'Cannot denormalize work log support.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogSupport->setDateTime(new \DateTimeImmutable());
            $workLogSupport->setSupportedBy($this->getUser());

            $workLogsSupports[] = $workLogSupport;
        }

        foreach ($workLogsSupports as $index => $specialLeaveWorkLogSupport) {
            $errors = $this->validator->validate($specialLeaveWorkLogSupport);

            if (count($errors) > 0) {
                return new JsonResponse(
                    ['detail' => sprintf('Work log support with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->specialWorkLogSupportService->createWorkLogSupport($workLogsSupports);
        $normalizedWorkLogsSupports = [];

        foreach ($workLogsSupports as $item) {
            $normalizedWorkLogsSupports[] = $this->normalizer->normalize(
                $item,
                $this->entityClassName,
                ['groups' => [$this->entityNormalizationGroup]]
            );
        }

        return new JsonResponse($normalizedWorkLogsSupports, JsonResponse::HTTP_CREATED);
    }
}
