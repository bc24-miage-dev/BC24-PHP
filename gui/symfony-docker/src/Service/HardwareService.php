<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\NetworkException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;


class HardwareService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function startReader(SessionInterface $session): ?Response {
        try {
            if (!$session->get('reader_started')) {
                $this->httpClient->request('GET', 'http://127.0.0.1:5000/startReader');
                $session->set('reader_started', true);
            }
        } catch (ClientException $e) {
            if ($e->getCode() === 403) {
                // Si l'erreur est une HTTP 403 (Forbidden), afficher un message personnalisé à l'utilisateur
                return new Response("Le scanner n'est pas activé, veuillez vérifier que le serveur run puis rafraîchir la page", Response::HTTP_FORBIDDEN);
            } else {
                // Pour d'autres types d'erreurs de client
                $this->logger->error('Erreur client lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
                return new Response('Une erreur s\'est produite lors de la requête HTTP (client error).', Response::HTTP_BAD_REQUEST);
            }
        } catch (ServerException $e) {
            // Pour les erreurs serveur
            $this->logger->error('Erreur serveur lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new Response('Une erreur s\'est produite lors de la requête HTTP (server error).', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (NetworkException $e) {
            // Pour les erreurs de réseau
            $this->logger->error('Erreur réseau lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new Response('Une erreur réseau s\'est produite lors de la requête HTTP.', Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Exception $e) {
            // Pour toutes les autres erreurs
            $this->logger->error('Erreur inattendue lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new Response('Une erreur inattendue s\'est produite lors de la requête HTTP.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return null;
    }


    public function stopReader(SessionInterface $session): ?Response {
        try {
            if ($session->get('reader_started')) {
                $this->httpClient->request('GET', 'http://127.0.0.1:5000/stopReader');
                $session->set('reader_started', false);
                return new Response('Reader stopped successfully.', Response::HTTP_OK);
            } else {
                return new Response('Reader was not started, so nothing to stop.', Response::HTTP_OK);
            }
        } catch (ClientException $e) {
            if ($e->getCode() === 403) {
                return new Response("Le scanner ne s'est pas stoppé, veuillez lancer une requête 'curl -X GET http://127.0.0.1:5000/stopReader'", Response::HTTP_FORBIDDEN);
            } else {
                $this->logger->error('Erreur lors de la requête HTTP : ' . $e->getMessage());
                return new Response('Une erreur s\'est produite lors de la requête HTTP.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (ServerException $e) {
            // Pour les erreurs serveur
            $this->logger->error('Erreur serveur lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new Response('Une erreur s\'est produite lors de la requête HTTP (server error).', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (NetworkException $e) {
            // Pour les erreurs de réseau
            $this->logger->error('Erreur réseau lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new Response('Une erreur réseau s\'est produite lors de la requête HTTP.', Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Exception $e) {
            // Pour toutes les autres erreurs
            $this->logger->error('Erreur inattendue lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new Response('Une erreur inattendue s\'est produite lors de la requête HTTP.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
