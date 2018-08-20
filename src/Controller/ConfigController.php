<?php

namespace App\Controller;

use App\Entity\Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConfigController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @return Response
     */
    public function config(): Response
    {
        $normalizedConfig = $this->normalizer->normalize(
            new Config(),
            Config::class,
            ['groups' => ['config_out']]
        );

        return JsonResponse::create($normalizedConfig, JsonResponse::HTTP_OK);
    }
}
