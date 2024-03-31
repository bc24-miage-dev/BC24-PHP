<?php

namespace App\Controller;

use App\Handlers\PictureHandler;
use App\Form\SearchType;
use App\Repository\ResourceRepository;
use App\Repository\UserResearchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Handlers\UserResearchHandler;

#[Route('/search')]
class SearchController extends AbstractController
{
    private EntityManagerInterface $entityManager;

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

    #[Route('/{id}', name: 'app_search_result')]
    public function result(int $id,
                           EntityManagerInterface $entityManager,
                           UserResearchRepository $userResearchRepository,
                           ResourceRepository $resourceRepository,
                           PictureHandler $pictureHandler,
                           Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $form->getData()->getId();
            return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
        }
        
        $resource = $resourceRepository->find($id);
        if (!$resource) {
            $this->addFlash('error', 'Aucune ressource trouvée avec cet identifiant');
            return $this->redirectToRoute('app_search');
        }

        $categoryName = $resource->getResourceName()->getResourceCategory()->getCategory();
        $imagePath = $pictureHandler->getImageForCategory($categoryName);

        if ($user = $this->getUser()) {
            $history = $userResearchRepository->findOneBy(['User' => $user, 'Resource' => $resourceRepository->find($id)]);
            if ($history) {
                $history->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                $entityManager->persist($history);
            } else {
                $handler = new UserResearchHandler();
                $entityManager->persist($handler->createUserResearch($user, $resource));
            }
            $entityManager->flush();
        }

        return $this->render('search/result.html.twig', [
            'form' => $form -> createView(),
            'resource' => $resource,
            'imagePath' => $imagePath,
        ]);
    }


}
