<?php

namespace App\Controller;

use App\Entity\Config;
use App\Service\ConfigService;
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
     * @var ConfigService
     */
    private $configService;

    /**
     * @param NormalizerInterface $normalizer
     * @param ConfigService $configService
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ConfigService $configService
    ) {
        $this->normalizer = $normalizer;
        $this->configService = $configService;
    }

    /**
     * @return Response
     */
    public function config(): Response
    {
        $config = $this->configService->getConfig();

        $normalizedConfig = $this->normalizer->normalize(
            $config,
            Config::class,
            ['groups' => ['config_out']]
        );

        return JsonResponse::create($normalizedConfig, JsonResponse::HTTP_OK);
    }
}
