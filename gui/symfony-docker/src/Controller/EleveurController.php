<?php

namespace App\Controller;


use App\Form\EleveurBirthType;
use App\Form\EleveurWeightType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\OwnershipHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/eleveur')]
class EleveurController extends AbstractController
{

    private TransactionHandler $transactionHandler;

    public function __construct(TransactionHandler $handler)
    {
        $this->transactionHandler = $handler;
    }

    #[Route('/', name: 'app_eleveur_index')]
    public function index(ResourceRepository $resourceRepo): Response
    {
        $resource = $resourceRepo->findByWalletAddress($this->getUser()->getWalletAddress());

        return $this->render('pro/eleveur/index.html.twig', [
            'resource' => $resource,
        ]);
    }

    #[Route('/naissance', name: 'app_eleveur_naissance')]
    public function naissance(Request $request,
        EntityManagerInterface $entityManager): Response {
        $handler = new ResourceHandler();
        $resource = $handler->createDefaultNewResource($this->getUser());

        $form = $this->createForm(EleveurBirthType::class, $resource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'La naissance de votre animal a bien été enregistrée !');
            return $this->redirectToRoute('app_eleveur_index');
        }
        return $this->render('pro/eleveur/naissance.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/list', name: 'app_eleveur_list')]
    public function list(ResourceRepository $resourceRepo, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $NFC = $request->request->get('NFC');
            $animaux = $resourceRepo->findByWalletAddressAndNFC($this->getUser()->getWalletAddress(),$NFC);
            if($animaux == null){
                $this->addFlash('error', 'Cette ressoure ne vous appartient pas');
                return $this->redirectToRoute('app_eleveur_list');
            }
        }
        else{
        $animaux = $resourceRepo->findByWalletAddressCategory($this->getUser()->getWalletAddress(), 'ANIMAL');
        }
        return $this->render('pro/eleveur/list.html.twig',
            ['animaux' => $animaux]
        );
    }

    #[Route('/arrivage', name: 'app_eleveur_acquire')]
    public function acquisition(Request $request,
                                EntityManagerInterface $entityManager,
                                OwnershipAcquisitionRequestRepository $ownershipRepo,
                                ResourceRepository $resourceRepo,
                                OwnershipHandler $ownershipHandler): Response {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $resource =$resourceRepo->find($form->getData()->getId());
            if (!$resource || $resource->getCurrentOwner()->getWalletAddress() == $this->getUser()->getWalletAddress()) {
                $this->addFlash('error', 'Vous ne pouvez pas demander la propriété de cette ressource');
                return $this->redirectToRoute('app_eleveur_acquire');
            }
            if ($ownershipRepo->findOneBy(['requester' => $this->getUser(), 'resource' => $resource, 'state' => 'En attente'])){
                $this->addFlash('error', 'Vous avez déjà demandé la propriété de cette ressource');
                return $this->redirectToRoute('app_eleveur_acquire');
            }

            $ownershipHandler->ownershipRequestCreate($this->getUser(), $entityManager, $resource);
            $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            return $this->redirectToRoute('app_eleveur_acquire');
        }

        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/eleveur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }

    #[Route('/pesee/{id}', name: 'app_eleveur_weight')]
    public function weight(Request $request,
                           EntityManagerInterface $entityManager,
                           ResourceRepository $resourceRepo,
                           $id): Response {
        $resource = $resourceRepo->findOneBy(['id' => $id]);

        if (!$resource ||
            $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL' ||
            $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress())
        {
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        $form = $this->createForm(EleveurWeightType::class, $resource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'L\'animal a bien été pesé');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/weight.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/vaccine/{id}', name: 'app_eleveur_vaccine')]
    public function vaccine(Request $request,
        EntityManagerInterface $entityManager,
        ResourceRepository $resourceRepo,
        $id): Response {

        $resource = $resourceRepo->findOneBy(['id' => $id]);

        if (!$resource ||
            $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL' ||
            $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $newVaccine = $request->request->get('vaccine');
            $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $dateString = $date->format('Y-m-d');
            $resource->setDescription($resource->getDescription() . 'VACCIN|' . $newVaccine . '|' . $dateString . ';');
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'Le vaccin a bien été enregistré');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/vaccine.html.twig', ['id' => $id]);
    }

    #[Route('/nutrition/{id}', name: 'app_eleveur_nutrition')]
    public function nutrition(Request $request,
        EntityManagerInterface $entityManager,
        ResourceRepository $resourceRepo,
        $id): Response {

        $resource = $resourceRepo->findOneBy(['id' => $id]);

        if (!$resource ||
            $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL' ||
            $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $newNutrition = $request->request->get('nutrition');
            $resource->setDescription($resource->getDescription() . 'NUTRITION|' . $newNutrition . ';');
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'Votre animal a bien mangé');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/nutrition.html.twig', ['id' => $id]);
    }

    #[Route('/disease/{id}', name: 'app_eleveur_disease')]
    public function disease(Request $request,
        EntityManagerInterface $entityManager,
        ResourceRepository $resourceRepo,
        $id): Response {
        $resource = $resourceRepo->findOneBy(['id' => $id]);
        if (!$resource ||
            $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL' ||
            $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $newDisease = $request->request->get('disease');
            $beginDate = $request->request->get('dateBegin');

            $resource->setDescription($resource->getDescription() .
                'MALADIE|' . $newDisease . '|' . $beginDate . ';');

            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'La maladie a bien été enregistrée');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/disease.html.twig', ['id' => $id]);
    }

    #[Route('/specific/{id}', name: 'app_eleveur_specific')]
    public function specific(ResourceRepository $resourceRepository, $id) : Response
    {
        $animal = $resourceRepository->findOneBy(['id' => $id]);
        if (!$animal || $animal->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        return $this->render('pro/eleveur/specific.html.twig', ['animal' => $animal ]);
    }

    #[Route('/transaction', name: 'app_eleveur_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() ,'state' => 'En attente']);
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
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        finally {
            return $this->redirectToRoute('app_eleveur_transferList');
        }
    }


    #[Route('/transactionRefused/{id}', name: 'app_eleveur_transferRefused', requirements: ['id' => '\d+'])]
    public function transferRefused($id,): RedirectResponse
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

    #[Route('/transaction/all' , name: 'app_eleveur_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_eleveur_transferList');
        }
    }
}
