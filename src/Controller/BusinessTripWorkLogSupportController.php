<?php

namespace App\Controller;

use App\Entity\BusinessTripWorkLogSupport;
use App\Service\SpecialWorkLogSupportService;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BusinessTripWorkLogSupportController extends AbstractSpecialWorkLogSupportController
{
    public function __construct(
        DenormalizerInterface $denormalizer,
        NormalizerInterface $normalizer,
        SpecialWorkLogSupportService $specialWorkLogSupportService,
        ValidatorInterface $validator
    ) {
        parent::__construct(
            $denormalizer,
            $normalizer,
            $specialWorkLogSupportService,
            $validator,
            BusinessTripWorkLogSupport::class,
            'business_trip_work_log_support_out_detail'
        );
    }
}
