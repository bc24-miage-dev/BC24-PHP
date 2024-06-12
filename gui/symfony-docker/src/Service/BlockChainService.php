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

class BlockChainService
{

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
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

    public function filterByRole(String $role): array
    {
        $respponse = $this->httpClient->request('POST', 'http://127.0.0.1:8080/mintResource', [
            'json' => $body,
        ]);
        $data = json_decode($response->getContent(), true);
        $returnData = [];
        foreach ($data as $key => $value) {
            if ($value['role'] !== $role) {
                $returnData[$value["ressource_id"]] = $value["ressource_name"];
                // something like this i hope :
                // 1 => "sheep"
                // 2 => "cow"
                // 3 => "pig"
                // ...
            }
        }

        return $returnData;
    }
}