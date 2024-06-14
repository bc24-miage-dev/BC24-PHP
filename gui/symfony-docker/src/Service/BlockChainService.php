<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    public function mintResource(String $WalletAddress,
        int $resourceId,
        int $quantity = 1,
        array $metadata = [],
        array $ingredients = []
    ): String {
        try {
            $body = [
                'walletAddress' => $WalletAddress,
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
        bool $isContaminated = false): array {
        return [
            'isContaminated' => $isContaminated,
            'weight' => $weight,
            'price' => $price,
            'description' => $description,
            'genre' => $genre,
            'nutrition' => [],
        ];
    }

    public function getResourceTemplate(String $role): array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resourceTemplates?required_role=" . $role);
        $data = json_decode($response->getContent(), true);

        $returnData = [];
        foreach ($data as $numberOfArray => $datas) {
            $returnData[$datas["resource_name"]] = $datas["resource_id"];
        }
        return $returnData;
    }

    public function getResourceWalletAddress(String $WalletAddress): array
    {

        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/" . $WalletAddress . "?metaData=true");
        $data = json_decode($response->getContent(), true);
        return $data;
    }

    // ----------------------------------- Handler ----------------------------------- //
    // i let this here for now but it should be in another service later //
    public function getAllRessourceFromWalletAddress(String $WalletAddress, String $resourceType = null): array
    {
        $data = $this->getResourceWalletAddress($WalletAddress);
        dd($data);
        $returnData = [];
        foreach ($data as $key => $value) {
            $arrayTMP = [
                "tokenId" => $value["tokenId"],
                "resource_name" => $value["metaData"]["resource_name"],
                "quantity" => $value["balance"],
                "genre" => $value["metaData"]["genre"],
            ];
            $returnData[$key] = $arrayTMP;
        }
        // dd($returnData);
        return $returnData;
    }

}
