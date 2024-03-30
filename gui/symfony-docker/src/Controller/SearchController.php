<?php

namespace App\Controller;

use App\Repository\ResourceRepository;
use App\Repository\UserResearchRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Resource;
use App\Form\SearchType;
use App\Entity\UserResearch;



#[Route('/search')]
class SearchController extends AbstractController
{
#[Route('/', name: 'app_search')]
    public function search(Request $request): Response
    {
            $form = $this->createForm(SearchType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $id = $data->getId();

                return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
            }
            return $this->render('search/search.html.twig', [
                'form' => $form->createView()
            ]);
    }

    #[Route('/{id}', name: 'app_search_result')]
    public function result(int $id,
                           ManagerRegistry $doctrine,
                           UserResearchRepository $userResearchRepository,
                           ResourceRepository $resourceRepository,
                           Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form -> handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $history = $userResearchRepository->findBy(['User' => $user]);
            if (count($history) > 0) {
                $history[0]->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                $entityManager = $doctrine->getManager();
                $entityManager->persist($history[0]);
                $entityManager->flush();
            }
            else{
                $userResearch = new UserResearch();
                $userResearch->setUser($user);
                $userResearch->setResource($resourceRepository->find($id));
                $userResearch->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                $entityManager = $doctrine->getManager();
                $entityManager->persist($userResearch);
                $entityManager->flush();
            }

            $data = $form->getData();
            $id = $data->getId();
            return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
        }

        $resource = $resourceRepository->find($id);
        if (!$resource) {
            $this->addFlash('error', 'Aucune ressource trouvée avec cet identifiant');
            return $this->redirectToRoute('app_search');
        }
        return $this->render('search/result.html.twig', [
            'form' => $form -> createView(),
            'resource' => $resource
        ]);
    }
}




