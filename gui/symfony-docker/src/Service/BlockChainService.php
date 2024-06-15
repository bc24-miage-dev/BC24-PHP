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

    public function mintResource(
        String $WalletAddress,
        int $resourceId,
        int $quantity = 1,
        array $metadata = [],
        array $ingredients = []
    ): String {
        try {
            $body = [
                'from_wallet_address' => $WalletAddress,
                'resourceId' => $resourceId,
                'quantity' => $quantity,
                'metaData' => $metadata,
                'ingredients' => $ingredients, 
            ];
            $response = $this->httpClient->request('POST', 'http://127.0.0.1:8080/resource/mint', [
                'json' => $body,
            ]);

            return $response->getContent(); // Obtenir le JSON brut
        } catch (\Exception $e) {
            return "Error";
            // Handle exception
        }
    }

    public function metadataTemplate(
        int $weight = 0,
        int $price = 0,
        String $description = "NAN",
        String $genre = "NAN",
        bool $isContaminated = false,
        String $address = "NAN",
        String $birthDate = "NAN",
        array $nutrition = [],
        array $vaccin = []): array {
        return [
            'isContaminated' => $isContaminated,
            'weight' => $weight,
            'price' => $price,
            'description' => $description,
            'genre' => $genre,
            'address' => $address,
            'birthDate' => $birthDate,
            'nutrition' => $nutrition,
            'vaccin' => $vaccin
        ];
    }

    public function getResourceIDFromRole(String $role): array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/templates?required_role=" . $role);
        $data = $response->getContent() ;

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

    public function getResourceTemplate(int $resourceId , String $role) : array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/templates?resource_id=".$resourceId."&required_role=".$role);
        $data = json_decode($response->getContent(), true);
        return $data;
    }

    public function getMetaDataFromTokenId(int $tokenId) : array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/".$tokenId."/metadata");
        $data = json_decode($response->getContent(), true);
        return $data;
    }
    // ----------------------------------- Handler ----------------------------------- //
    // i let this here for now but it should be in another service later //
    public function getAllRessourceFromWalletAddress(String $WalletAddress, String $resourceType = null): array
    {
        $data = $this->getResourceWalletAddress($WalletAddress);
        $returnData = [];
        foreach ($data as $key => $value) {
            $arrayTMP = [
                "tokenId" => $value["tokenId"],
                "resource_name" => $value["metaData"]["resource_name"],
                "quantity" => $value["balance"],
                "isContaminated" => $value["metaData"]["data"][0]["stringData"]["isContaminated"],
                "weight" => $value["metaData"]["data"][0]["stringData"]["weight"],
                "price" => $value["metaData"]["data"][0]["stringData"]["price"],
                "description" => $value["metaData"]["data"][0]["stringData"]["description"],
                "genre" => $value["metaData"]["data"][0]["stringData"]["genre"],
                "address" => $value["metaData"]["data"][0]["stringData"]["address"],
                "birthDate" => $value["metaData"]["data"][0]["stringData"]["birthDate"],
                "nutrition" => $value["metaData"]["data"][0]["stringData"]["nutrition"],
                "vaccin" => $value["metaData"]["data"][0]["stringData"]["vaccin"],
            ];
            $returnData[$key] = $arrayTMP;
        }
        return $returnData;
    }

    public function getStringDataFromTokenID(int $tokenId): array
    {
        $data = $this->getMetaDataFromTokenId($tokenId);
        // dd($data);
        $returnData = $data["data"][0]["stringData"];
        // dd($returnData);
        return $returnData;
    }

}
