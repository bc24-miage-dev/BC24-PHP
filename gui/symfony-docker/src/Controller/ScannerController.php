<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\HardwareService;


class ScannerController extends AbstractController {

    #[Route('/start-reader', name: 'start_reader', methods: ['POST'])]
    public function startReader(HardwareService $hardwareService): JsonResponse
    {
        $response = $hardwareService->startReader();
        return $response;
    }
}
