<?php

namespace App\Controller;

use App\Handlers\PictureHandler;
use App\Form\SearchType;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Handlers\UserResearchHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\BlockChainService;


#[Route('/search')]
class SearchController extends AbstractController
{   
    private BlockChainService $blockChainService;

    public function __construct(BlockChainService $blockChainService)
    {
        $this->blockChainService = $blockChainService;
    }
    
    #[Route('/', name: 'app_search')]
    public function search(Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id = $form->getData()->getId();
            return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
        }

        return $this->render('search/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    

    #[Route('/{id}', name: 'app_search_result', requirements: ['id' => '\d+'])]
    public function result(int $id,
                           ResourceRepository $resourceRepository,
                           PictureHandler $pictureHandler,
                           UserResearchHandler $userResearchHandler,
                           Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $form->getData()->getId();
            return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
        }
        $resource =$this->blockChainService->getRessourceFromTokenId($id);

        switch ($resource["resourceType"]) {
            case 'Animal':
                $resources = $this->blockChainService->getResourceFromTokenIDAnimal($id);
                break;
            case "Carcass":
                $resources = $this->blockChainService->getResourceFromTokenIDCarcass($id);
                break;
            case "Demi Carcass":
                $resources = $this->blockChainService->getResourceFromTokenIDDemiCarcass($id);
                break;
            case "Meat":
                $resources = $this->blockChainService->getResourceFromTokenIDMeat($id);
                break;
            case "Product":
                $resources = $this->blockChainService->getResourceFromTokenIDProduct($id);
                break;
            default:
                $this->addFlash('error', 'Ressource non reconnue');
                return $this->redirectToRoute('app_transporteur_list');
                break;
        }
        return $this->render('search/result.html.twig', [
            'form' => $form -> createView(),
            'resource' => $resources,
        ]);
    }


}
