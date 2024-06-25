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
            $response = $this->httpClient->request('GET', 'http://127.0.0.1:5000/startReader');
            $data = json_decode($response->getContent(), true); // Décoder le JSON brut
            return new JsonResponse(['data' => $data]);
            
            //TO BE COMMENTED
            /*sleep(3);
            $data = [
                "NFT_tokenID" => 11111,
                "date_creation" => "2024-05-08",
                "date_derniere_modification" => "2024-05-30",
                "gps" => "{'latitude': 0.0, 'longitude': 0.0, 'altitude': None}",
                "temperature" => "28.05",
                "uid" => "BA:44:47:73",
            ];
            return new JsonResponse(['data' => $data]);*/

        } catch (ClientException $e) {
            if ($e->getCode() === 403) {
                // Si l'erreur est une HTTP 403 (Forbidden), afficher un message personnalisé à l'utilisateur
                return new JsonResponse(["message" => "Le scanner n'est pas activé, veuillez vérifier que le serveur run puis rafraîchir la page"], JsonResponse::HTTP_FORBIDDEN);
            } else {
                // Pour d'autres types d'erreurs de client
                $this->logger->error('Erreur client lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
                return new JsonResponse(["message" => "Une erreur s'est produite lors de la requête HTTP (client error)."], JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (ServerException $e) {
            // Pour les erreurs serveur
            $this->logger->error('Erreur serveur lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(["message" => "Une erreur s'est produite lors de la requête HTTP (server error)."], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (NetworkException $e) {
            // Pour les erreurs de réseau
            $this->logger->error('Erreur réseau lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(["message" => "Une erreur réseau s'est produite lors de la requête HTTP."], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Exception $e) {
            // Pour toutes les autres erreurs
            $this->logger->error('Erreur inattendue lors de la requête HTTP : ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(["message" => "Une erreur inattendue s'est produite lors de la requête HTTP."], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        return null;        
    }


    public function write($tokenID): JsonResponse
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
        $data = json_decode($response->getContent(), true); // Décoder le JSON brut
        // dd($data);
        try {
            
                $response = new JsonResponse();
                $response->setData=["result" => true];
                $response->setStatusCode(JsonResponse::HTTP_OK);
            return $response;
            
        } catch (Execption $e) {
            return new JsonResponse(['result' => false]);
        }
        return new JsonResponse(['result' => false]);
        } 
        catch(Exetion $e){
            return new JsonResponse(['result' => false]);
        }
        return new JsonResponse(['result' => false]);
    }

    public function test(){
        $response = new JsonResponse();
                $response->setData=["result" => "true"];
                $response->setStatusCode(JsonResponse::HTTP_OK);
            return $response;
    }
}