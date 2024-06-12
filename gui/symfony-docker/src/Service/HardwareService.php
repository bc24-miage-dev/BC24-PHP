<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\NetworkException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HardwareService
{

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function startReader(): JsonResponse
    {
        try {
            //TO BE DECOMMENTED
            //$response = $this->httpClient->request('GET', 'http://127.0.0.1:5000/startReader');
            //return $response->getContent(); // Obtenir le JSON brut

            //TO BE COMMENTED
            $data = [
                "NFT_tokenID" => 11111,
                "date_creation" => "2024-05-08",
                "date_derniere_modification" => "2024-05-30",
                "gps" => "{'latitude': 0.0, 'longitude': 0.0, 'altitude': None}",
                "temperature" => "28.05",
                "uid" => "BA:44:47:73",
            ];
            return new JsonResponse(['data' => $data]);

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

    public function mintResource(int $resourceId,
                                 int $quantity = 1,
                                 array $metadata = [],
                                array $ingredients = []
                                ): String
    {
        try {
            $body = [
                'resourceId' => $resourceId,
                'quantity' => $quantity,
                'metaData' => $metadata, // Empty associative array
                'ingredients' => $ingredients, // Empty associative array
            ];

            $response = $this->httpClient->request('POST', 'http://127.0.0.1:8080/mintResource', [
                'json' => $body,
            ]);

            return $response->getContent(); // Obtenir le JSON brut
        } catch (\Exception $e) {
            return "Error";
            // Handle exception
        }
    }

    public function metadataTemplate(int $weight = 0, 
                                    int $price = 0, 
                                    String $description = "NAN",
                                    String $genre = "NAN",
                                    bool $isContaminated = false ): array
    {
        return [
            'isContaminated' => $isContaminated,
            'weight' => $weight,
            'price' => $price,
            'description' => $description,
            'genre' => $genre,
            'nutrition' => []
        ];
    }

    public function write($tokenId): String
    {
        try {
            $url = 'http://127.0.0.1:5000/write';
            $data = [
            'NFT_tokenID' => $tokenID,
        ];

        // Faire la requête POST
        $response = $this->httpClient->request('POST', $url, [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
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

}
