<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\WorkMonth;
use App\Repository\ContractRepository;
use App\Repository\WorkMonthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContractController extends AbstractController
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ContractRepository
     */
    private $contractRepository;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        EntityManagerInterface $entityManager,
        ContractRepository $contractRepository,
        WorkMonthRepository $workMonthRepository
    ) {
        $this->normalizer = $normalizer;
        $this->entityManager = $entityManager;
        $this->contractRepository = $contractRepository;
        $this->workMonthRepository = $workMonthRepository;
    }

    public function terminateContract(Request $request, int $id): Response
    {
        $contract = $this->contractRepository->getRepository()->find($id);
        if (!$contract || !$contract instanceof Contract) {
            throw $this->createNotFoundException(sprintf('Contract with id %d was not found.', $id));
        }

        $data = json_decode((string) $request->getContent());
        if (!isset($data->dateTime)) {
            return JsonResponse::create(
                ['detail' => 'Date time is missing.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data->dateTime);
        if (!$dateTime) {
            return JsonResponse::create(
                ['detail' => 'Date time format is invalid.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($dateTime && (new \DateTimeImmutable()) > $dateTime->setTime(23, 59, 59)) {
            return JsonResponse::create(
                ['detail' => 'Unable to terminate contract. Date time is in the past.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if (
            $dateTime < $contract->getStartDateTime()->setTime(0, 0, 0, 0)
            || ($contract->getEndDateTime() && $dateTime > $contract->getEndDateTime()->setTime(23, 59, 59))
        ) {
            return JsonResponse::create(
                ['detail' => 'Date time is out of contract range.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $filteredContracts = $this->workMonthRepository->findAllOpenedByUserBetweenDates(
            $contract->getUser(),
            $dateTime->setTime(0, 0, 0),
            $contract->getEndDateTime()
        );

        if (count($filteredContracts) > 0) {
            return JsonResponse::create(
                [
                    'detail' => 'Unable to terminate contract. There are opened work months after entered date.',
                    'openedWorkMonths' => $this->normalizer->normalize(
                        $filteredContracts,
                        WorkMonth::class,
                        ['groups' => ['work_month_out_list']]
                    ),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $contract->setEndDateTime($dateTime);
        $this->entityManager->flush();

        return JsonResponse::create(null, JsonResponse::HTTP_OK);
    }
}
