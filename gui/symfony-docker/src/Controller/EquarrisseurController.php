<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\ResourceOwnerChangerType;
use App\Repository\ResourceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/equarrisseur')]
class EquarrisseurController extends AbstractController
{
    #[Route('/', name: 'app_equarrisseur_index')]
    public function index(): Response
    {
        return $this->render('pro/equarrisseur/index.html.twig');
    }

    #[Route('/acquisition', name: 'app_equarrisseur_acquire')]
    public function acquisition(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EQUARRISSEUR');

        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $id = $data->getId();

            $resource = $doctrine->getRepository(Resource::class)->find($id);
            if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL') {
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à un animal');
                return $this->redirectToRoute('app_equarrisseur_acquire');
            }

            $resource->setCurrentOwner($this->getUser());
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'L\'animal a bien été enregistré');
            return $this->redirectToRoute('app_equarrisseur_acquire');
        }
        return $this->render('pro/equarrisseur/acquire.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/list/{category}', name: 'app_equarrisseur_list')]
    public function list(ManagerRegistry $doctrine, String $category) : Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted( attribute: 'ROLE_EQUARRISSEUR');
        $repository = $doctrine->getRepository(Resource::class);
        $resources = $repository->findByOwnerAndResourceCategory($user, strtoupper($category));
        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources ]
        );
    }
}
