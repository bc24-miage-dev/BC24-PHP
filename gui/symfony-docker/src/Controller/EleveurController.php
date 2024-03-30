<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\EleveurBirthType;
use App\Form\EleveurWeightType;
use App\Form\ResourceModifierType;
use App\Form\ResourceOwnerChangerType;
use App\Form\ResourceType;
use App\Handlers\proAcquireHandler;
use App\Handlers\ResourceHandler;
use App\Repository\ResourceFamilyRepository;
use App\Repository\ResourceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/eleveur')]
class EleveurController extends AbstractController
{
    #[Route('/', name: 'app_eleveur_index')]
    public function index(): Response
    {
        return $this->render('pro/eleveur/index.html.twig');
    }

    #[Route('/naissance', name: 'app_eleveur_naissance')]
    public function naissance(Request $request,
                              ManagerRegistry $doctrine): Response
    {
        $handler = new ResourceHandler();
        $resource = $handler->createDefaultNewResource($this->getUser());

        $form = $this->createForm(EleveurBirthType::class, $resource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'La naissance de votre animal a bien été enregistrée !');
            return $this->redirectToRoute('app_eleveur_index');
        }
        return $this->render('pro/eleveur/naissance.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/list', name: 'app_eleveur_list')]
    public function list(ResourceRepository $resourceRepo) : Response
    {
        $animaux = $resourceRepo->findByOwnerAndResourceCategory($this->getUser(), 'ANIMAL');
        return $this->render('pro/eleveur/list.html.twig',
            ['animaux' => $animaux]
        );
    }

    #[Route('/arrivage', name: 'app_eleveur_acquire')]
    public function acquisition(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proAcquireHandler = new proAcquireHandler();

            if($proAcquireHandler->acquireStrict($form, $doctrine, $this->getUser(), 'ANIMAL')){
                $this->addFlash('success', 'L\'animal a bien été enregistré');
            } else {
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à un animal');
            }
            return $this->redirectToRoute('app_eleveur_acquire');
        }

        return $this->render('pro/eleveur/acquire.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/pesee/{id}', name: 'app_eleveur_weight')]
    public function weight(Request $request,
                           ManagerRegistry $doctrine,
                           ResourceRepository $resourceRepo,
                           $id): Response
    {
        $resource = $resourceRepo->findOneBy(['id' => $id, 'currentOwner' => $this->getUser()]);

        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL') {
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        $form = $this->createForm(EleveurWeightType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'L\'animal a bien été pesé');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/weight.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/vaccine/{id}', name: 'app_eleveur_vaccine')]
    public function vaccine(Request $request,
                            ManagerRegistry $doctrine,
                            ResourceRepository $resourceRepo,
                            $id): Response{

        $resource = $resourceRepo->findOneBy(['id' => $id, 'currentOwner' => $this->getUser()]);

        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL') {
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $newVaccine = $request->request->get('vaccine');
            $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $dateString = $date->format('Y-m-d');
            $resource->setDescription($resource->getDescription() . 'VACCIN|' . $newVaccine . '|' . $dateString . ';');
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'Le vaccin a bien été enregistré');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/vaccine.html.twig', ['id' => $id]);
    }

    #[Route('/disease/{id}', name: 'app_eleveur_disease')]
    public function disease(Request $request,
                            ManagerRegistry $doctrine,
                            ResourceRepository $resourceRepo,
                            $id): Response
    {
        $resource = $resourceRepo->findOneBy(['id' => $id, 'currentOwner' => $this->getUser()]);
        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL') {
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à un de vos animaux');
            return $this->redirectToRoute('app_eleveur_list');
        }

        if ($request->isMethod('POST')) {
            $newDisease = $request->request->get('disease');
            $beginDate = $request->request->get('dateBegin');
            $endDate = $request->request->get('dateEnd');

            $resource->setDescription($resource->getDescription() .
                'MALADIE|' . $newDisease . '|' . $beginDate . '|' . $endDate . ';');

            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'La maladie a bien été enregistrée');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/disease.html.twig', ['id' => $id]);
    }
}
