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
    public function ScanNFC(Request $request, $id): Response
{
    if ($request->isMethod('POST')) {       //if post method
        // The form is submitted
        $response = $this->hardwareService->write($id);
        // Do something with the data
        if ($response->getStatusCode() === 200) {
            $this->addFlash('success', 'The data has been saved');
        } else {
            $this->addFlash('error', 'An error occurred while saving the data');
        }

    }

    return $this->render('user/ScanNFC.html.twig', [
        'id' => $id,
    ]);
}
}
