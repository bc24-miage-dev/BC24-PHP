<?php

namespace App\Controller;


use App\Entity\Resource;
use App\Form\EleveurBirthType;
use App\Form\EleveurWeightType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\EleveurHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\ResourcesListHandler;
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
    private EleveurHandler $eleveurHandler;
    private EntityManagerInterface $entityManager;
    private ResourceRepository $resourceRepository;

    public function __construct(TransactionHandler $handler,
                                EleveurHandler $eleveurHandler,
                                EntityManagerInterface $entityManager,
                                ResourceRepository $resourceRepository)
    {
        $this->transactionHandler = $handler;
        $this->eleveurHandler = $eleveurHandler;
        $this->entityManager = $entityManager;
        $this->resourceRepository = $resourceRepository;
    }

    #[Route('/', name: 'app_eleveur_index')]
    public function index(): Response
    {
        return $this->render('pro/eleveur/index.html.twig');
    }

    #[Route('/naissance', name: 'app_eleveur_naissance')]
    public function naissance(Request $request,
                              ResourceHandler $handler): Response
    {
        $form = $this->createForm(EleveurBirthType::class,
            $resource = $handler->createDefaultNewResource($this->getUser()));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlash('success', 'La naissance de votre animal a bien été enregistrée !');
            return $this->redirectToRoute('app_eleveur_index');
        }
        return $this->render('pro/eleveur/naissance.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/list', name: 'app_eleveur_list')]
    public function list(Request $request,
                         ResourcesListHandler $listHandler): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $animaux = $listHandler->getSpecificResource($request->request->get('NFC'), $this->getUser());
            }
            catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_eleveur_list');
            }
        }
        else{
            $animaux = $listHandler->getResources($this->getUser(), 'ANIMAL');
        }

        return $this->render('pro/eleveur/list.html.twig',
            ['animaux' => $animaux]
        );
    }

    #[Route('/arrivage', name: 'app_eleveur_acquire')]
    public function acquisition(Request $request,
                                OwnershipAcquisitionRequestRepository $ownershipRepo): Response {

        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionHandler->askOwnership($form->getData()->getId(), $this->getUser());
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
            'requests' => $requests
        ]);
    }

    #[Route('/pesee/{id}', name: 'app_eleveur_weight')]
    public function weight(Request $request,
                           $id): Response {

        $resource = $this->resourceRepository->findOneBy(['id' => $id]);
        if (!$this->eleveurHandler->isAllowedToTouch($resource, $this->getUser()))
        {
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        $form = $this->createForm(EleveurWeightType::class, $resource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($resource);
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'animal a bien été pesé');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/weight.html.twig', [
            'form' => $form->createView(),
            'id' => $id
        ]);
    }

    #[Route('/vaccine/{id}', name: 'app_eleveur_vaccine')]
    public function vaccine(Request $request,
                            $id): Response {

        $resource = $this->resourceRepository->findOneBy(['id' => $id]);

        if (!$this->eleveurHandler->isAllowedToTouch($resource, $this->getUser())){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $this->eleveurHandler->vaccineAnimal($request->request->get('vaccine'), $resource);
            $this->addFlash('success', 'Le vaccin a bien été enregistré');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/vaccine.html.twig', ['id' => $id]);
    }

    #[Route('/nutrition/{id}', name: 'app_eleveur_nutrition')]
    public function nutrition(Request $request,
                              $id): Response
    {
        $resource = $this->resourceRepository->findOneBy(['id' => $id]);

        if (!$this->eleveurHandler->isAllowedToTouch($resource, $this->getUser())){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }
        if ($request->isMethod('POST')) {
            $this->eleveurHandler->addNutrition($request->request->get('nutrition'), $resource);
            $this->addFlash('success', 'Le changement de nutrition de votre animal a bien été enregistré');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/nutrition.html.twig', ['id' => $id]);
    }

    #[Route('/disease/{id}', name: 'app_eleveur_disease')]
    public function disease(Request $request,
                            $id): Response
    {
        $resource = $this->resourceRepository->findOneBy(['id' => $id]);
        if (!$this->eleveurHandler->isAllowedToTouch($resource, $this->getUser())){
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $this->eleveurHandler->addDisease($request->request->get('disease'),
                $request->request->get('dateBegin'), $resource);
            $this->addFlash('success', 'La maladie a bien été enregistrée');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/disease.html.twig', ['id' => $id]);
    }

    #[Route('/specific/{id}', name: 'app_eleveur_specific')]
    public function specific(ResourceRepository $resourceRepository,
                             $id) : Response
    {
        $animal = $resourceRepository->findOneBy(['id' => $id]);
        if (!$this->eleveurHandler->isAllowedToTouch($animal, $this->getUser())){
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
