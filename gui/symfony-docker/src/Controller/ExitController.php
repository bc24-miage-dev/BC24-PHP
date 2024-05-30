<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\HardwareService;


class ExitController extends AbstractController
{
    private HardwareService $hardwareService;

    public function __construct(HardwareService $hardwareService)
    {
        $this->hardwareService = $hardwareService;
    }


    #[Route('/exit', name: 'app_exit')]
    public function exit(SessionInterface $session): Response
    {
        $response = $this->hardwareService->stopReader($session);
        return $response;
    }
}
