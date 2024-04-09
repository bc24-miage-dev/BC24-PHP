<?php

namespace App\Controller;

use App\Handlers\proAcquireHandler;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ResourceOwnerChangerType;
use App\Entity\Resource;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

#[Route('/pro/transporteur')]
class TransporteurController extends AbstractController
{
    #[Route('/', name: 'app_transporteur_index')]
    public function index(): Response
    {
        return $this->render('pro/transporteur/index.html.twig');
    }


    #[Route('/acquisition', name: 'app_transporteur_acquire')]
    public function acquisition(Request $request,
                                ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proAcquireHandler = new proAcquireHandler();

            if($proAcquireHandler->acquire($form, $doctrine, $this->getUser())){
                $this->addFlash('success', 'La ressource a bien été enregistrée');
            } else {
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à une ressource');
            }
            return $this->redirectToRoute('app_transporteur_acquire');
        }
        return $this->render('pro/transporteur/acquire.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/list', name: 'app_transporteur_list')]
    public function list(ResourceRepository $resourceRepo,
                         Request $request) : Response
    {
        if ($request->isMethod('POST')) {
            $NFC = $request->request->get('NFC');
            $resources = $resourceRepo->findByWalletAddressAndNFC($this->getUser()->getWalletAddress(),$NFC);
            if($resources == null){
                $this->addFlash('error', 'Cette ressoure ne vous appartient pas');
                return $this->redirectToRoute('app_transporteur_list');
            }
        }
        else{
        $resources = $resourceRepo->findByWalletAddress($this->getUser()->getWalletAddress());
        }

        return $this->render('pro/transporteur/list.html.twig',
            ['resources' => $resources]
        );
    }


}
