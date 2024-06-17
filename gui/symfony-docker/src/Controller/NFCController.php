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
    public function startWriteNFC(Request $request, $id): Response
{
    if ($request->isMethod('POST')) {       //if post method
        $response = $this->hardwareService->write($id);
        if ($response == 200) {
            $this->addFlash('success', 'The NFT ID of the resource has been write on the NFC');
        } 
        else {
            $this->addFlash('error', 'An error occurred while writing the resource NFT ID on the NFC');
        }
    }
    return $this->render('user/WriteOnNFC.html.twig', [
        'id' => $id,
    ]);
}
}
