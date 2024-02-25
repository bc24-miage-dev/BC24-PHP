<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Resource;
use App\Form\SearchType;



#[Route('/search')]
class SearchController extends AbstractController
{
   
    #[Route('/', name: 'app_search')]
    public function search(Request $request, ManagerRegistry $doctrine): Response{
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $id = $data->getId();
            $resource = $doctrine
                ->getRepository(Resource::class)
                ->find($id);
            if (!$resource) {
                $this->addFlash('error', 'Resource not found');
                return $this->redirectToRoute('app_search');
            }
            return $this->redirectToRoute('app_search', ['id' => $id]);
        }
        return $this->render('search/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_search_result')]
    public function searchResult($id, ManagerRegistry $doctrine): Response{
        $resource = $doctrine
            ->getRepository(Resource::class)
            ->find($id);
        if (!$resource) {
            $this->addFlash('error', 'Resource not found');
            return $this->redirectToRoute('app_search');
        }
        return $this->render('search/search_result.html.twig', [
            'resource' => $resource,
        ]);
    }

}
    

    

