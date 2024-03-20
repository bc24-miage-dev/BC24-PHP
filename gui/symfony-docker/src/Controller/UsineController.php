<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\ResourceOwnerChangerType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/usine')]
class UsineController extends AbstractController
{
    #[Route('/', name: 'app_usine_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USINE');
        return $this->render('pro/usine/index.html.twig');
    }

    #[Route('/arrivage', name:'app_usine_acquire')]
    public function acquire(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USINE');

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

}
