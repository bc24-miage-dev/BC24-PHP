<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ExitController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/exit', name: 'app_exit')]
    public function exit(SessionInterface $session): Response
    {
        if ($session->get('reader_started')) {
            try {
                // APPEL STOPREADER
                //$this->httpClient->request('GET', 'http://127.0.0.1:5000/stopReader');
                $session->set('reader_started', false);
                return new Response('OK');
            } catch (ClientException $e) {
                if ($e->getCode() === 403) {
                    return new Response("Le scanner ne s'est pas stoppé, veuillez lancer une requête 'curl -X GET http://127.0.0.1:5000/stopReader'", Response::HTTP_FORBIDDEN);
                } else {
                    $errorMessage = $e->getMessage();
                    $this->logger->error('Erreur lors de la requête HTTP : ' . $errorMessage);
                    return new Response('Une erreur s\'est produite lors de la requête HTTP.', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } else {
            return new Response('Le reader n\'était pas démarré, donc rien à couper.', Response::HTTP_OK);
        }
    }
}
