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
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



#[Route('/search')]
class SearchController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    
    #[Route('/', name: 'app_search')]
    public function search(Request $request, SessionInterface $session): Response
    {
        try {
            // APPEL STARTREADER
            //$this->httpClient->request('GET', 'http://127.0.0.1:5000/startReader');
            $session->set('reader_started', true);
        } catch (ClientException $e) {
            if ($e->getCode() === 403) {
                // Si l'erreur est une HTTP 403 (Forbidden), afficher un message personnalisé à l'utilisateur
                return new Response("Le scanner n'est pas activé, veuillez rafraîchir la page", Response::HTTP_FORBIDDEN);
            } else {
                // Pour d'autres types d'erreurs
                $errorMessage = $e->getMessage();
                $this->logger->error('Erreur lors de la requête HTTP : ' . $errorMessage);
                // Ou afficher un message d'erreur générique à l'utilisateur
                return new Response('Une erreur s\'est produite lors de la requête HTTP.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


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

        $resource = $resourceRepository->find($id);
        if (!$resource) {
            $this->addFlash('error', 'Aucune ressource trouvée avec cet identifiant');
            return $this->redirectToRoute('app_search');
        }
        $userResearchHandler->userResearchRecordingProcess($this->getUser(), $resource);

        return $this->render('search/result.html.twig', [
            'form' => $form -> createView(),
            'resource' => $resource,
            'imagePath' => $pictureHandler->getImageForCategory(
                $resource->getResourceName()->getResourceCategory()->getCategory())
        ]);
    }


}
