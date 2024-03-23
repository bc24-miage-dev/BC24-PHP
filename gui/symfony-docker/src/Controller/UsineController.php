<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Form\ResourceOwnerChangerType;
use App\Repository\ResourceFamilyRepository;
use App\Repository\ResourceNameRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

#[Route('/pro/usine')]
class UsineController extends AbstractController
{
    #[Route('/', name: 'app_usine_index')]
    public function index(): Response
    {
        return $this->render('pro/usine/index.html.twig');
    }

    #[Route('/arrivage', name:'app_usine_acquire')]
    public function acquire(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $id = $data->getId();

            $resource = $doctrine->getRepository(Resource::class)->find($id);
            if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'DEMI-CARCASSE') {
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à une demi-carcasse');
                return $this->redirectToRoute('app_usine_acquire');
            }

            $resource->setCurrentOwner($this->getUser());
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'La demi-carcasse a bien été enregistrée comme étant vôtre');
            return $this->redirectToRoute('app_usine_acquire');
        }
        return $this->render('pro/usine/acquire.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/list', name: 'app_usine_list')]
    public function list(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Resource::class);

        $resources = $repository->findByOwnerAndResourceCategory($this->getUser(), 'DEMI-CARCASSE');
        return $this->render('pro/usine/list.html.twig', [
            'resources' => $resources
        ]);
    }

    #[Route('/decoupe/{id}', name: 'app_usine_decoupe')]
    public function decoupe(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $resourceRepository = $doctrine->getRepository(Resource::class);
        $resource = $resourceRepository->find($id);

        if (!$resource || $resource->getCurrentOwner() != $this->getUser() ||
            $resource->getResourceName()->getResourceCategory()->getCategory() != 'DEMI-CARCASSE') {

            $this->addFlash('error', 'Ce tag NFC ne correspond pas à une demi-carcasse');
            return $this->redirectToRoute('app_usine_list');
        }

        $nameRepository = $doctrine->getRepository(ResourceName::class);
        $resources = $nameRepository->findByCategoryAndFamily(category: 'MORCEAU',
            family: $resourceRepository->find($id)->getResourceName()->getFamily()->getName());

        if ($request->isMethod('POST')) {
            $entityManager = $doctrine->getManager();
            $i = 1;
            while ($request->request->has('tag' . $i)) {
                $id = $request->request->get('tag' . $i); //Tag NFC
                $weight = $request->request->get('weight' . $i); //Poids
                $name = $request->request->get('name' . $i); //Nom (Poitrine, Côte, etc.)

                $childResource = $this->createChildResource($doctrine, $resource);
                $childResource->setWeight($weight);
                $childResource->setId($id);
                $childResource->setResourceName($this->searchInArrayByName($resources, $name));

                $entityManager->persist($childResource);
                $entityManager->flush();
                $i++;
            }

            $resource->setIsLifeCycleOver(true);
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'La demi-carcasse a bien été découpée');
            return $this->redirectToRoute('app_usine_list');
        }

        return $this->render('pro/usine/decoupe.html.twig', [
            'demiCarcasse' => $resource, // La demi-carcasse à découper
            'morceauxPossibles' => $resources // Les ressources possibles à partir d'elle
        ]);
    }

    #[Route('/creationRecette/name', name: 'app_usine_creationRecetteName', methods: ['POST'])]
    public function creationRecetteName(Request $request, ResourceFamilyRepository $repoFamily, ResourceNameRepository $repoName): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $family = $request->request->get('family');
            return $this->redirectToRoute('app_usine_creationRecetteIngredients', ['name' => $name, 'family' => $family]);
        }
        $families = $repoFamily->findAll();
        return $this->render('pro/usine/creationRecetteName.html.twig',
        [
            'families' => $families
        ]);
    }

    #[Route('/creationRecette/ingredients/{name}/{family}', name: 'app_usine_creationRecetteIngredients')]
    public function creationRecette(Request $request, $name, $family): Response
    {
        return $this->render('pro/usine/creationRecetteIngredients.html.twig', [
            'name' => $name
        ]);
    }

    private function createChildResource(ManagerRegistry $doctrine, Resource $resource): Resource
    {
        $newChildResource = new Resource();
        $newChildResource->setCurrentOwner($this->getUser());
        $newChildResource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $newChildResource->setIsLifeCycleOver(false);
        $newChildResource->setIsContamined(false);
        $newChildResource->setPrice(0);
        $newChildResource->setOrigin($this->getUser()->getProductionSite());
        $newChildResource->setDescription('');
        $newChildResource->addComponent($resource);
        return $newChildResource;
    }

    private function searchInArrayByName($array, $nameString): ?ResourceName
    {
        foreach ($array as $item) {
            if ($item->getName() == $nameString) {
                return $item;
            }
        }
        return null;
    }

}
