<?php

namespace App\Controller;

use App\Entity\OwnershipAcquisitionRequest;
use App\Form\EleveurBirthType;
use App\Form\EleveurDisease2Type;
use App\Form\EleveurDiseaseType;
use App\Form\EleveurNutritionType;
use App\Form\EleveurVaccineType;
use App\Form\EleveurWeightType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\EleveurHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ProductionSiteRepository;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use App\Service\BlockChainService;
use App\Service\HardwareService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/pro/eleveur')]
class EleveurController extends AbstractController
{
    private TransactionHandler $transactionHandler;
    private EleveurHandler $eleveurHandler;
    private EntityManagerInterface $entityManager;
    private ResourceRepository $resourceRepository;
    private HttpClientInterface $httpClient;
    private BlockChainService $blockChainService;
    private HardwareService $hardwareService;
    private UserRepository $userRepository;
    private OwnershipAcquisitionRequest $ownershipAcquisitionRequest;
    private ProductionSiteRepository $productionSiteRepository;

    public function __construct(TransactionHandler $handler,
        EleveurHandler $eleveurHandler,
        EntityManagerInterface $entityManager,
        ResourceRepository $resourceRepository,
        HttpClientInterface $httpClient,
        HardwareService $hardwareService,
        BlockChainService $blockChainService,
        UserRepository $userRepository,
        ProductionSiteRepository $productionSiteRepository
    ) {
        $this->transactionHandler = $handler;
        $this->eleveurHandler = $eleveurHandler;
        $this->entityManager = $entityManager;
        $this->resourceRepository = $resourceRepository;
        $this->httpClient = $httpClient;
        $this->hardwareService = $hardwareService;
        $this->blockChainService = $blockChainService;
        $this->userRepository = $userRepository;
        $this->productionSiteRepository = $productionSiteRepository;

    }

    #[Route('/', name: 'app_eleveur_index')]
    public function index(): Response
    {
        return $this->render('pro/eleveur/index.html.twig');
    }

    #[Route('/naissance', name: 'app_eleveur_naissance')]
    public function naissance(Request $request,
        ResourceHandler $handler): Response {
        $form = $this->createForm(EleveurBirthType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $productionSite = $this->productionSiteRepository->findOneby(["id" => $this->getUser()->getProductionSite()->getId()]);
            $metadata = $this->blockChainService->metadataTemplateAnimal([
                "weight" => (int) $form->getData()["weight"],
                "price" => (int) $form->getData()["price"],
                "description" => $form->getData()["description"],
                "genre" => $form->getData()["Genre"],
                "address" => $productionSite->getAddress(),
                "birthPlace" => $productionSite->getCountry(),
                "birthDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                "approvalNumberBreeder" => $productionSite->getApprovalNumber(),
            ]
            );
            $response = $this->blockChainService->mintResource($this->getUser()->getWalletAddress(), (int) $form->getData()["resourceName"], 1, $metadata);
            $responseArray = json_decode($response, true);
            $this->addFlash('success', 'La naissance de votre ' . $responseArray["ressourceName"] . ' a bien été enregistrée ! NFT : ' . $responseArray["tokenId"]);

            return $this->render('user/WriteOnNFC.html.twig', [
                'id' => [$responseArray['tokenId']],
                'name' => [$responseArray['ressourceName']],
                'resourceType' => 'Animal',
            ]);
        }

        return $this->render('pro/eleveur/naissance.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/list', name: 'app_eleveur_list')]
    public function list(Request $request,
        ResourcesListHandler $listHandler): Response {
        if ($request->isMethod('POST')) {
            $resources = $this->blockChainService->getRessourceFromTokenId($request->request->get('NFC'));
            if ($resources == []) {
                $this->addFlash('error', 'Aucune ressource trouvée');
                return $this->redirectToRoute('app_eleveur_list');
            }
            if($resources['current_owner'] != $this->getUser()->getWalletAddress()){
                $this->addFlash('error', 'Vous n\'êtes pas le propriétaire de cette ressource');
                return $this->redirectToRoute('app_eleveur_list');
            }
            return $this->redirectToRoute('app_eleveur_specific', ['id' => $request->request->get('NFC')]);
        } else {
            $animaux = $this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress());
        }
        return $this->render('pro/eleveur/list.html.twig', ['animaux' => $animaux]);
    }

    #[Route('/arrivage', name: 'app_eleveur_acquire')]
    public function acquisition(Request $request,
        OwnershipAcquisitionRequestRepository $ownershipRepo): Response {

        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionHandler->askOwnership($this->getUser(), $form->getData()['Owner'], $form->getData()["id"]);

                $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            } finally {
                return $this->redirectToRoute('app_eleveur_acquire');
            }
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/eleveur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests,
        ]);
    }

    #[Route('/pesee/{id}', name: 'app_eleveur_weight')]
    public function weight(Request $request,
        $id): Response {
        $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
        $form = $this->createForm(EleveurWeightType::class, null, ['id' => $id, 'weight' => $resource["weight"]]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $replaceMetaData = $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $id, ["weight" => $form->getData()["weight"]]);
            sleep(5);
            $this->addFlash('success', 'Poids mise à jour');
            return $this->redirectToRoute('app_eleveur_specific', ['id' => $id]);
        }
        return $this->render('pro/eleveur/weight.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }

    #[Route('/vaccine/{id}', name: 'app_eleveur_vaccine')]
    public function vaccine(Request $request,
        $id): Response {
        $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
        $form = $this->createForm(EleveurVaccineType::class, null, ['id' => $id]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vaccin = $resource["vaccin"];
            $vaccin[$form->getData()["Vaccin"]] = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $replaceMetaData = $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $id, ["vaccin" => $vaccin]);
            sleep(5);
            $this->addFlash('success', 'Vaccin mise à jour');
            return $this->redirectToRoute('app_eleveur_specific', ['id' => $id]);
        }
        return $this->render('pro/eleveur/vaccine.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }

    #[Route('/nutrition/{id}', name: 'app_eleveur_nutrition')]
    public function nutrition(Request $request,
        $id): Response {
        $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
        $form = $this->createForm(EleveurNutritionType::class, null, ['id' => $id, 'nutrition' => $resource['nutrition']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $replaceMetaData = $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $id, ["nutrition" => $form->getData()["nutrition"]]);
            $this->addFlash('success', 'Nutrition mise à jour');
            return $this->redirectToRoute('app_eleveur_specific', ['id' => $id]);
        }
        return $this->render('pro/eleveur/nutrition.html.twig', [
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/disease/{id}', name: 'app_eleveur_disease')]
    public function disease(Request $request,
        $id): Response {
        $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
        $form = $this->createForm(EleveurDiseaseType::class, null, ['id' => $id, 'isContaminated' => $resource['isContaminated']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $replaceMetaData = $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $id, ["isContaminated" => $form->getData()["isContaminated"]]);
            $this->addFlash('success', 'Maladie mise à jour');
            return $this->redirectToRoute('app_eleveur_specific', ['id' => $id]);
        }
        return $this->render('pro/eleveur/disease.html.twig', [
            'form' => $form->createView(),
            'id' => $id]);
    }

    #[Route('/disease2/{id}', name: 'app_eleveur_disease2')]
    public function disease2(Request $request,
        $id): Response {
        $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
        $form = $this->createForm(EleveurDisease2Type::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $Disease2 = $resource["disease"];
            $Disease2[$form->getData()["Disease2"]] = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $replaceMetaData = $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $id, ["disease" => $Disease2]);
            sleep(5);
            $this->addFlash('success', 'Maladie mise à jour');
            return $this->redirectToRoute('app_eleveur_specific', ['id' => $id]);
        }
        return $this->render('pro/eleveur/disease2.html.twig', [
            'form' => $form->createView(),
            'id' => $id]);
    }

    #[Route('/specific/{id}', name: 'app_eleveur_specific')]
    public function specific(ResourceRepository $resourceRepository,
        $id): Response {
        $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
        return $this->render('pro/eleveur/specific.html.twig', ['animal' => $resource]);
    }

    #[Route('/transaction', name: 'app_eleveur_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser(), 'state' => 'En attente']);
        $pastTransactions = $requestRepository->findPastRequests($this->getUser());
        return $this->render('pro/eleveur/transferList.html.twig',
            ['requests' => $requests, 'pastTransactions' => $pastTransactions]
        );
    }

    #[Route('/transaction/{id}', name: 'app_eleveur_transfer', requirements: ['id' => '\d+'])]
    public function transfer($id): RedirectResponse
    {
        try {

            $this->transactionHandler->acceptTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction effectuée');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_eleveur_transferList');
        }
    }

    #[Route('/transactionRefused/{id}', name: 'app_eleveur_transferRefused', requirements: ['id' => '\d+'])]
    public function transferRefused($id): RedirectResponse
    {
        try {
            $this->transactionHandler->refuseTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction refusée avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_eleveur_transferList');
        }
    }

    #[Route('/transaction/all', name: 'app_eleveur_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_eleveur_transferList');
        }
    }
}
