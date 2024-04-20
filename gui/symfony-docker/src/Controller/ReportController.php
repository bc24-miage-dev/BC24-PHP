<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Handlers\ReportHandler;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Report;

class ReportController extends AbstractController
{
    #[Route('/report/reportAliment', name: 'app_report_reportAliment')]
    public function report(Request $request,
                           ResourceRepository $resourceRepository,
                           EntityManagerInterface $entityManager,
                           ReportHandler $handler): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($request->isMethod('POST')) {
            $resource = $resourceRepository->find($request->request->get('tag'));
            if (! $resource){ // Check if the resource exists
                $this->addFlash('error', 'La ressource n\'existe pas');
                return $this->redirectToRoute('app_report_reportAliment');
            }
            $report = $handler->createReport($this->getUser(), $resource, $request->request->get('description'));
            $entityManager->persist($report);
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a bien été enregistré');
            return $this->redirectToRoute('app_index');
        }
        return $this->render('report/report.html.twig');
    }
}
