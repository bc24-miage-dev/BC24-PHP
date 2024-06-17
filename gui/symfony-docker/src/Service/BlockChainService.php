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
        $data = json_decode($response->getContent(), true);

        $returnData = [];
        foreach ($data as $numberOfArray => $datas) {
            $returnData[$datas["resource_name"]] = $datas["resource_id"];
        }
        return $returnData;
    }

    public function getResourceWalletAddress(String $WalletAddress): array
    {
        $WalletAddress = "0x9AC65C5FF92e9C52fA342fA9D8e681637A4C80e0";
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/" . $WalletAddress . "?metaData=true");
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }

    public function getResourceTemplate(int $resourceId , String $role) : array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/templates?resource_id=".$resourceId."&required_role=".$role);
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }

    public function getMetaDataFromTokenId(int $tokenId) : array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/".$tokenId."/metadata");
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }
    // ----------------------------------- Handler ----------------------------------- //
    // i let this here for now but it should be in another service later //
    public function getAllRessourceFromWalletAddress(String $WalletAddress, String $resourceType = null): array
    {
        $data = $this->getResourceWalletAddress($WalletAddress);
        $returnData = [];
        // dd($data);
        foreach ($data as $key => $value) {
            // dd($resourceType == $value["metaData"]["resource_type"]);
            if ($resourceType != null && $value["metaData"]["resource_type"] != $resourceType) {
                continue;
            }
            $arrayTMP = [
                "tokenId" => $value["tokenId"],
                "resource_name" => $value["metaData"]["resource_name"],
                "resource_type" => $value["metaData"]["resource_type"],
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

    public function getRessourceFromTokenId(int $tokenId): array
    {
        $data = $this->getMetaDataFromTokenId($tokenId);
        // dd($data);
        $returnData = [
            "tokenID" => $tokenId,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"], 
            // "quantity" => $data["data"][0]["balance"],
            "isContaminated" => $data["data"][0]["stringData"]["isContaminated"],
            "weight" => $data["data"][0]["stringData"]["weight"],
            "price" => $data["data"][0]["stringData"]["price"],
            "description" => $data["data"][0]["stringData"]["description"],
            "genre" => $data["data"][0]["stringData"]["genre"],
            "address" => $data["data"][0]["stringData"]["address"],
            "birthDate" => $data["data"][0]["stringData"]["birthDate"],
            "nutrition" => $data["data"][0]["stringData"]["nutrition"],
            "vaccin" => $data["data"][0]["stringData"]["vaccin"],
        ];
        // dd($returnData);
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
    //return all the possible resource from a certain resourceID, role and resourceType
    //for example, if we want to get all the possible from a certain resourceID,
    //this function will return all the possible resource from this resourceID
    public function getPossibleResourceFromResourceID(int $resourceId, String $role, String $resourceType): array
    {
        $response = $this->httpClient->request('GET', "http://127.0.0.1:8080/resource/templates?required_role=".$role);
        $data = json_decode($response->getContent(), true);
        $count = 0;
        $returnData = [];
        // dd($data);
        foreach ($data as $numberOfArray => $datas) {
            // dd($datas["resource_id"], $resourceId);
            // dd($datas["resource_type"] == $resourceType);
            if ($datas["resource_type"] == $resourceType){
                // dd($datas["needed_resources"]);
                foreach ($datas["needed_resources"] as $key => $value) {
                    // dd($value, $resourceId, $key);
                    if ($value == $resourceId) {
                        $returnData[$count++] = $datas;
                    }
                }
            }
        }
        // dd($returnData);
        if ($count ==0){
            return [0 => 
                ["resource_id" => -1]
            ];
        }
        return $returnData;
    }

    //get the metadata of a token and replace the metadata with the new one
    //merge the old metadata with the new one
    //in other word, replace the metadata of a token if there is metadata in this section
    //but fill the metadata if there is no metadata in this section
    public function replaceMetaData($walletAddress,int $tokenId, array $metadata): array
    {
        $response = $this->getMetaDataFromTokenId($tokenId);
        // dd($response);
        $oldMetaData = $response["data"][0]["stringData"];
        // dd($oldMetaData);
        $mergedMetaData = array_merge($oldMetaData, $metadata);
        // dd($mergedMetaData);

        $body = [
            "from_wallet_address" => $walletAddress,
            "tokenId" => $tokenId,
            "metaData" => $mergedMetaData,
        ];
        // dd($body);
        $response = $this->httpClient->request('POST', "http://127.0.0.1:8080/resource/metadata", [
            'json' => $body,
        ]);
        // dd($response->getContent());
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData);
        return $returnData;
    }
}
