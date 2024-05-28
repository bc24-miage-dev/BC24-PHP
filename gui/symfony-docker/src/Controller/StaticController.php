<?php

namespace App\Controller;

use App\Form\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class StaticController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request, SessionInterface $session): Response
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
            $data = $form->getData();
            $id = $data->getId();
            return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
        }

        return $this->render('static/index.html.twig', [
            'form' => $form->createView(),
            'apiData' => $data, // Passer les données API à la vue si nécessaire
        ]);
    }


    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('static/about.html.twig');
    }

    #[Route('/siteInfo', name: 'app_info')]
    public function info(): Response
    {
        return $this->render('static/info.html.twig');
    }
}
