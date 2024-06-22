<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\HardwareService;

class NFCController extends AbstractController
{

    private HardwareService $hardwareService;

    public function __construct(HardwareService $hardwareService)
    {
        $this->hardwareService = $hardwareService;
        
    }

    #[Route('/NFC/write/{id}', name: 'app_nfc_write')]
    public function startWriteNFC(Request $request, $id): Response //page to render
    {
        $arrayList = explode('.', $id);
        return $this->render('user/WriteOnNFC.html.twig', [
            'id' => $arrayList,
        ]);
    }

    #[Route('/writeNFC/{id}', name: 'app_write')]    //called method of JS
        public function WriteNFC(Request $request, $id): response
    {
            $response = $this->hardwareService->write($id);
            return $this->render("/static/info.html.twig");
    }

    #[Route('/NFC/test', name: 'app_nfc_read')]
    public function testNFC(): response
    {
        $test = (implode('.', [1,2,3,4,5,6,7,8,9,10]));
        $test2 = explode('.', $test);
        return $this->redirectToRoute('app_nfc_write', ['id' => $test]);
    }

    #[Route('/NFC/{id}', name: 'app_nfc_readjs')]
    public function testNFCjs($id): response
    {
        $this->hardwareService->write($id);
        return $this->render("/static/info.html.twig");
    }
}