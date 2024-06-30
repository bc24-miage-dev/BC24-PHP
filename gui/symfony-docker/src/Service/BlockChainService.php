<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BlockChainService
{

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private String $baseURL = "http://127.0.0.1:8080/";

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
            $response = $this->httpClient->request('POST', $this->baseURL."resource/mint", [
                'json' => $body,
            ]);

            return $response->getContent(); // Obtenir le JSON brut
        } catch (\Exception $e) {
            return "Error";
            // Handle exception
        }
    }

    public function metadataTemplate(array $information): array {

            $templateArray = [
                'isContaminated' => false,
                'disease' => null,
                'weight' => null,
                'price' => null,
                'description' => null,
                'genre' => null,
                'address' => null,
                'birthPlace' => null,
                'birthDate' => null,
                'nutrition' => null,
                'vaccin' => null,
                'approvalNumberBreeder' => null,
                //----------------------------------Animal----------------------------------//
                'slaughteringPlace' => null,
                'carcassDate' => null,
                'slaughtererCountry' => null,
                'approvalNumberSlaughterer' => null,
                //----------------------------------Carcass----------------------------------//
                'demiCarcassDate' => null,
                //----------------------------------DemiCarcass----------------------------------//
                'manufacturingPlace' => null,
                'meatDate' => null,
                'manufactureingCountry' => null,
                'approvalNumberManufacturer' => null,
                //----------------------------------Manufacturing----------------------------------//
                'transportDate1' => null,
                'transportDate2' => null,
                'travelTime' => null,
                //----------------------------------Transport----------------------------------//
            ];
            $mergedMetaData = array_merge($templateArray, $information);
        return $mergedMetaData;
    }

    public function metadataTemplateAnimal(array $information) : array {
        $templateArray = [
            'isContaminated' => false,
            'disease' => null,
            'weight' => null,
            'price' => null,
            'description' => null,
            'genre' => null,
            'address' => null,
            'birthPlace' => null,
            'birthDate' => null,
            'nutrition' => null,
            'vaccin' => null,
            'approvalNumberBreeder' => null,
            'slaughteringPlace' => null,
            'carcassDate' => null,
            'slaughtererCountry' => null,
            'approvalNumberSlaughterer' => null,
            'transportDate1' => null,
            'transportDate2' => null,
            'travelTime' => null,
            "gpsStart" => null,
            "gpsEnd" => null,
            "temperatureStart" => null,
            "temperatureEnd" => null,
        ];
        $mergedMetaData = array_merge($templateArray, $information);
        return $mergedMetaData;
    }

    public function metadataTemplateCarcass(array $information) : array {
        $templateArray = [
            'isContaminated' => false,
            'slaughteringPlace' => null,
            'carcassDate' => null,
            'slaughtererCountry' => null,
            'approvalNumberSlaughterer' => null,
            'demiCarcassDate' => null,
            'transportDate1' => null,
            'transportDate2' => null,
            'travelTime' => null,
            "gpsStart" => null,
            "gpsEnd" => null,
            "temperatureStart" => null,
            "temperatureEnd" => null,
        ];
        $mergedMetaData = array_merge($templateArray, $information);
        return $mergedMetaData;
    }

    public function metadataTemplateDemiCarcass(array $information) : array {
        $templateArray = [
            'isContaminated' => false,
            'demiCarcassDate' => null,
            'manufacturingPlace' => null,
            'slaughtererCountry' => null,
            'meatDate' => null,
            'manufactureingCountry' => null,
            'approvalNumberManufacturer' => null,
            'transportDate1' => null,
            'transportDate2' => null,
            'travelTime' => null,
            "gpsStart" => null,
            "gpsEnd" => null,
            "temperatureStart" => null,
            "temperatureEnd" => null,
        ];
        $mergedMetaData = array_merge($templateArray, $information);
        return $mergedMetaData;
    }

    public function metadataTemplateMeat(array $information) : array {
        $templateArray = [
            'isContaminated' => false,
            'manufacturingPlace' => null,
            'meatDate' => null,
            'manufactureingCountry' => null,
            'approvalNumberManufacturer' => null,
            'transportDate1' => null,
            'transportDate2' => null,
            'travelTime' => null,
            "gpsStart" => null,
            "gpsEnd" => null,
            "temperatureStart" => null,
            "temperatureEnd" => null,
        ];
        $mergedMetaData = array_merge($templateArray, $information);
        return $mergedMetaData;
    }
    
    public function metadataTemplateRecipe(array $information) : array {
        $templateArray = [
            'isContaminated' => false,
            'manufacturingPlace' => null,
            'recipeDate' => null,
            'manufactureingCountry' => null,
            'approvalNumberManufacturer' => null,
            'transportDate1' => null,
            'transportDate2' => null,
            'travelTime' => null,
            "gpsStart" => null,
            "gpsEnd" => null,
            "temperatureStart" => null,
            "temperatureEnd" => null,
        ];
        $mergedMetaData = array_merge($templateArray, $information);
        return $mergedMetaData;
    }

    public function getResourceIDFromRole(String $role): array
    {
        $response = $this->httpClient->request('GET', $this->baseURL."resource/templates?required_role=" . $role);
        $data = json_decode($response->getContent(), true);

        $returnData = [];
        foreach ($data as $numberOfArray => $datas) {
            $returnData[$datas["resource_name"]] = $datas["resource_id"];
        }
        return $returnData;
    }

    public function getResourceWalletAddress(String $WalletAddress): array
    {
        $response = $this->httpClient->request('GET', $this->baseURL."resource/" . $WalletAddress . "?metaData=true");
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }

    public function getResourceTemplate(int $resourceId , String $role = null) : array
    {
        if( $role == null && $resourceId == 0){
            $response = $this->httpClient->request('GET', $this->baseURL."resource/templates");
        }
        elseif ($resourceId == 0){
            $response = $this->httpClient->request('GET', $this->baseURL."resource/templates?required_role=".$role);
        }
        elseif ($role == null){
            $response = $this->httpClient->request('GET', $this->baseURL."resource/templates?resource_id=".$resourceId);
        }
        else{
            $response = $this->httpClient->request('GET', $this->baseURL."resource/templates?resource_id=".$resourceId."&required_role=".$role);
        }
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }

    public function getMetaDataFromTokenId(int $tokenId) : array
    {
        $response = $this->httpClient->request('GET', $this->baseURL."resource/".$tokenId."/metadata");
        
        if($response->getStatusCode() == 500){
            return [];
        }
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }

    public function mintToMany(String $walletAddress,int $tokenID, array $metaData) : array
    {
        $body = [
            "from_wallet_address" => $walletAddress,
            "producer_token_id" => $tokenID,
            "metaData" => $metaData,
        ];
        // dd($body);
        $response = $this->httpClient->request('POST', $this->baseURL."resource/mintToMany", [
            'json' => $body,
        ]);
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData); 
        return $returnData;
    }

    public function createWalletAddress(): String
    {
        $response = $this->httpClient->request('GET', $this->baseURL."wallet/static");
        $data = json_decode($response->getContent(), true);
        return $data["wallet_address"];
    }

    public function TransferResource(int $tokenId, int $quantity, String $fromWalletAddress, String $toWalletAddress): array
    {
        $body = [
            "tokenId" => $tokenId,
            "quantity" => $quantity,
            "from_wallet_address" => $fromWalletAddress,
            "to_wallet_address" => $toWalletAddress,
        ];
        $response = $this->httpClient->request('POST', $this->baseURL."resource/transfer", [
            'json' => $body,
        ]);
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData);
        return $returnData;
    }

    public function assignRole(String $walletAddress, String $role): array
    {
        $body = [
            "from_wallet_address" => "0xFE3B557E8Fb62b89F4916B721be55cEb828dBd73",
            "target_wallet_address" => $walletAddress,
            "role" => $role,
        ];
        $response = $this->httpClient->request('POST', $this->baseURL."roles/assignRole", [
            'json' => $body,
        ]);
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData);
        return $returnData;
    }

    public function getRole(String $walletAddress): array
    {
        $response = $this->httpClient->request('GET', $this->baseURL."roles/".$walletAddress);
        $data = json_decode($response->getContent(), true);
        // dd($data);
        return $data;
    }

    public function giveETHToWalletAddress(String $walletAddress) : array
    {
        $body = [
            "sender_address" => "0xFE3B557E8Fb62b89F4916B721be55cEb828dBd73",
            "receiver_address" => $walletAddress,
            "amount" => 10,
        ];
        $response = $this->httpClient->request('POST', $this->baseURL."wallet/send-eth", [
            'json' => $body,
        ]);
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData);
        return $returnData;
    }
    // ----------------------------------- Handler ----------------------------------- //
    // i let this here for now but it should be in another service later //
    
    public function getAllRessourceFromWalletAddress(String $WalletAddress, String $resourceType = null): array
    {
        $data = $this->getResourceWalletAddress($WalletAddress);
        $returnData = [];
        
        foreach ($data as $key => $value) {
            // dd($resourceType == $value["metaData"]["resource_type"]);
            if ($resourceType != null && $value["metaData"]["resource_type"] != $resourceType) {
                continue;
            }
            
            $stringDataPath = $value["metaData"]["data"][count($value["metaData"]["data"])-1]["stringData"];
            $arrayTMP = [
                "tokenId" => $value["tokenId"] ,
                "resource_name" => $value["metaData"]["resource_name"],
                "resource_type" => $value["metaData"]["resource_type"]
            ];
            $returnData[$key] = $arrayTMP;
        }
        return $returnData;
    }

    public function getRessourceFromTokenId(int $tokenId): array
    {
        $data = $this->getMetaDataFromTokenId($tokenId);
        // dd($data);
        if ($data == []){
            return [];
        }
        $returnData = [
            "tokenID" => $tokenId,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"],
            "current_owner" => $data["current_owner"],

        ];
        // dd($returnData);
        return $returnData;
    }

    

    public function getResourceFromTokenIDAnimal(int $tokenID) : array
    {
        $data = $this->getMetaDataFromTokenId($tokenID);
        $stringDataPath = $data["data"][count($data["data"])-1]["stringData"];
        $returnData = [
            "tokenID" => $tokenID,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"], 
            "isContaminated" => $stringDataPath["isContaminated"],
            "disease" => $stringDataPath["disease"],
            "weight" => $stringDataPath["weight"],
            "price" => $stringDataPath["price"],
            "description" => $stringDataPath["description"],
            "genre" => $stringDataPath["genre"],
            "address" => $stringDataPath["address"],
            "birthPlace" => $stringDataPath["birthPlace"],
            "birthDate" => $stringDataPath["birthDate"],
            "nutrition" => $stringDataPath["nutrition"],
            "vaccin" => $stringDataPath["vaccin"],
            "approvalNumberBreeder" => $stringDataPath["approvalNumberBreeder"],
            "slaughteringPlace" => $stringDataPath["slaughteringPlace"],
            "carcassDate" => $stringDataPath["carcassDate"],
            "slaughtererCountry" => $stringDataPath["slaughtererCountry"],
            "approvalNumberSlaughterer" => $stringDataPath["approvalNumberSlaughterer"],
            "transportDate1" => $stringDataPath["transportDate1"],
            "transportDate2" => $stringDataPath["transportDate2"],
            "travelTime" => $stringDataPath["travelTime"],
            "listOfIngredients" => $data["ingredients"],
            "gpsStart" => $stringDataPath["gpsStart"],
            "gpsEnd" => $stringDataPath["gpsEnd"],
            "temperatureStart" => $stringDataPath["temperatureStart"],
            "temperatureEnd" => $stringDataPath["temperatureEnd"],
        ];
        // if($data["ingredients"] == []){ // if there is no ingredients
        //     $returnData["listOfIngredients"] = null;
        // }
        // else{
        //     $returnData["listOfIngredients"] => $data["ingredients"]:
        // }
        return $returnData;
    }
    
    public function getResourceFromTokenIDCarcass(int $tokenID) : array
    {
        $data = $this->getMetaDataFromTokenId($tokenID);
        $stringDataPath = $data["data"][count($data["data"])-1]["stringData"];
        $returnData = [
            "tokenID" => $tokenID,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"], 
            "isContaminated" => $stringDataPath["isContaminated"],
            "slaughteringPlace" => $stringDataPath["slaughteringPlace"],
            "carcassDate" => $stringDataPath["carcassDate"],
            "slaughtererCountry" => $stringDataPath["slaughtererCountry"],
            "approvalNumberSlaughterer" => $stringDataPath["approvalNumberSlaughterer"],
            "demiCarcassDate" => $stringDataPath["demiCarcassDate"],
            "transportDate1" => $stringDataPath["transportDate1"],
            "transportDate2" => $stringDataPath["transportDate2"],
            "travelTime" => $stringDataPath["travelTime"],
            "listOfIngredients" => $data["ingredients"],
            "gpsStart" => $stringDataPath["gpsStart"],
            "gpsEnd" => $stringDataPath["gpsEnd"],
            "temperatureStart" => $stringDataPath["temperatureStart"],
            "temperatureEnd" => $stringDataPath["temperatureEnd"],
        ];
        return $returnData;
    }

    public function getResourceFromTokenIDDemiCarcass(int $tokenID) : array
    {
        $data = $this->getMetaDataFromTokenId($tokenID);
        // dd($data);
        $stringDataPath = $data["data"][count($data["data"])-1]["stringData"];
        $returnData = [
            "tokenID" => $tokenID,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"], 
            "isContaminated" => $stringDataPath["isContaminated"],
            "demiCarcassDate" => $stringDataPath["demiCarcassDate"],
            "slaughteringPlace" => $stringDataPath["slaughteringPlace"],
            "slaughtererCountry" => $stringDataPath["slaughtererCountry"],
            "approvalNumberSlaughterer" => $stringDataPath["approvalNumberSlaughterer"],
            "manufacturingPlace" => $stringDataPath["manufacturingPlace"],
            "meatDate" => $stringDataPath["meatDate"],
            "manufactureingCountry" => $stringDataPath["manufactureingCountry"],
            "approvalNumberManufacturer" => $stringDataPath["approvalNumberManufacturer"],
            "transportDate1" => $stringDataPath["transportDate1"],
            "transportDate2" => $stringDataPath["transportDate2"],
            "travelTime" => $stringDataPath["travelTime"],
            "listOfIngredients" => $data["ingredients"],
            "gpsStart" => $stringDataPath["gpsStart"],
            "gpsEnd" => $stringDataPath["gpsEnd"],
            "temperatureStart" => $stringDataPath["temperatureStart"],
            "temperatureEnd" => $stringDataPath["temperatureEnd"],
        ];
        return $returnData;
    }

    public function getResourceFromTokenIDMeat(int $tokenID) : array
    {
        $data = $this->getMetaDataFromTokenId($tokenID);
        $stringDataPath = $data["data"][count($data["data"])-1]["stringData"];
        $returnData = [
            "tokenID" => $tokenID,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"], 
            "isContaminated" => $stringDataPath["isContaminated"],
            "manufacturingPlace" => $stringDataPath["manufacturingPlace"],
            "meatDate" => $stringDataPath["meatDate"],
            "manufactureingCountry" => $stringDataPath["manufactureingCountry"],
            "approvalNumberManufacturer" => $stringDataPath["approvalNumberManufacturer"],
            "transportDate1" => $stringDataPath["transportDate1"],
            "transportDate2" => $stringDataPath["transportDate2"],
            "travelTime" => $stringDataPath["travelTime"],
            "listOfIngredients" => $data["ingredients"],
            "gpsStart" => $stringDataPath["gpsStart"],
            "gpsEnd" => $stringDataPath["gpsEnd"],
            "temperatureStart" => $stringDataPath["temperatureStart"],
            "temperatureEnd" => $stringDataPath["temperatureEnd"],
        ];
        return $returnData;
    }

    public function getResourceFromTokenIDRecipe(int $tokenID) : array
    {
        $data = $this->getMetaDataFromTokenId($tokenID);
        $stringDataPath = $data["data"][count($data["data"])-1]["stringData"];
        $returnData = [
            "tokenID" => $tokenID,
            "resourceID" => $data["resource_id"],
            "resourceName" => $data["resource_name"],
            "resourceType" => $data["resource_type"], 
            "isContaminated" => $stringDataPath["isContaminated"],
            "manufacturingPlace" => $stringDataPath["manufacturingPlace"],
            "manufactureingCountry" => $stringDataPath["manufactureingCountry"],
            "approvalNumberManufacturer" => $stringDataPath["approvalNumberManufacturer"],
            "transportDate1" => $stringDataPath["transportDate1"],
            "transportDate2" => $stringDataPath["transportDate2"],
            "travelTime" => $stringDataPath["travelTime"],
            "listOfIngredients" => $data["ingredients"],
            "gpsStart" => $stringDataPath["gpsStart"],
            "gpsEnd" => $stringDataPath["gpsEnd"],
            "temperatureStart" => $stringDataPath["temperatureStart"],
            "temperatureEnd" => $stringDataPath["temperatureEnd"],
        ];
        return $returnData;
    }


    public function getStringDataFromTokenID(int $tokenId): array
    {
        $data = $this->getMetaDataFromTokenId($tokenId);
        // dd($data);
        $returnData = $data["data"][count($data["data"])-1]["stringData"];
        // dd($returnData);
        return $returnData;
    }
    //return all the possible resource from a certain resourceID, role and resourceType
    //for example, if we want to get all the possible from a certain resourceID,
    //this function will return all the possible resource from this resourceID
    public function getPossibleResourceFromResourceID(int $resourceId, String $role, String $resourceType): array
    {
        $response = $this->httpClient->request('GET', $this->baseURL."resource/templates?required_role=".$role);
        $data = json_decode($response->getContent(), true);
        $count = 0;
        $returnData = [];
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
                ["resource_id" => $resourceId]
            ];
        }
        return $returnData;
    }

    //get the metadata of a token and replace the metadata with the new one
    //merge the old metadata with the new one
    //in other word, replace the metadata of a token if there is metadata in this section
    //but fill the metadata if there is no metadata in this section
    public function replaceMetaData(String $walletAddress,int $tokenId, array $metadata): array
    {
        $response = $this->getMetaDataFromTokenId($tokenId);
        // dd($response);
        $oldMetaData = $response["data"][count($response["data"])-1]["stringData"];
        // dd($oldMetaData);
        $mergedMetaData = array_merge($oldMetaData, $metadata);
        // dd($mergedMetaData);

        $body = [
            "from_wallet_address" => $walletAddress,
            "tokenId" => $tokenId,
            "metaData" => $mergedMetaData,
        ];
        $response = $this->httpClient->request('POST', $this->baseURL."resource/metadata", [
            'json' => $body,
        ]);        
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData, $body);
        return $returnData;
    }

    public function replaceMetaDataTransport($walletAddress,int $tokenId): array
    {
        $response = $this->getMetaDataFromTokenId($tokenId);
        $roleReceiver = $this->getRole($walletAddress);

        if($roleReceiver["role"] == "TRANSPORTER"){
            $metadata["transportDate1"] = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            // sleep(5);
            // $metadata["transportDate2"] = json_decode(json_encode(new \DateTime('now', new \DateTimeZone('Europe/Paris')),true),true);
            // $metadata["travelTime"] = $metadata["transportDate2"]->diff($metadata["transportDate1"])->format('%a days %H:%I:%S');
        }
        else{
            $metadata["transportDate2"] = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        }
        
        // dd($response);
        $oldMetaData = $response["data"][count($response["data"])-1]["stringData"];
        // dd($oldMetaData);
        $mergedMetaData = array_merge($oldMetaData, $metadata);
        // dd($mergedMetaData);
        if(isset($mergedMetaData["transportDate1"]) && isset($mergedMetaData["transportDate2"])){
            // Ensure both dates are DateTime objects
            $date1 = $mergedMetaData["transportDate1"] instanceof \DateTime ? $mergedMetaData["transportDate1"] : new \DateTime($mergedMetaData["transportDate1"]["date"], new \DateTimeZone('Europe/Paris'));
            $date2 = $mergedMetaData["transportDate2"] instanceof \DateTime ? $mergedMetaData["transportDate2"] : new \DateTime($mergedMetaData["transportDate2"]["date"]);
            // Now calculate the difference
            $mergedMetaData["travelTime"] = $date2->diff($date1)->format('%a days %H:%I:%S');
        }
        $body = [
            "from_wallet_address" => $walletAddress,
            "tokenId" => $tokenId,
            "metaData" => $mergedMetaData,
        ];

        $response = $this->httpClient->request('POST', $this->baseURL."resource/metadata", [
            'json' => $body,
        ]);
        $returnData = json_decode($response->getContent(), true);
        // dd($returnData);
        return $returnData;
    }

    public function getAllRecipe($role){
        $data = $this->getResourceTemplate(0 , $role);
        $returnData = [];
        foreach ($data as $numberOfArray => $datas) {
            if($datas["resource_type"] == "Product"){
                array_push($returnData, $datas);
            }
        }
        return $returnData;
    }

    public function getRecipe(int $resourceId): array
    {
        $data = $this->getResourceTemplate($resourceId , "MANUFACTURER"); //return only one recipe
        // dd($data[0]);
        return $data[0];
    }

    public function getResourceListInformation(array $listOfTokenId): array
    {
        $returnData = [];
        foreach ($listOfTokenId as $key => $value) {
            $data = $this->getResourceTemplate($value);
            array_push($returnData, $data);
        }
        return $returnData;
    }
    
}