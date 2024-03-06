<?php

namespace App\Controller;

use App\Entity\Resource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Report;
use App\Form\ReportType;

class ReportController extends AbstractController
{
    #[Route('/report/reportAliment', name: 'app_report_reportAliment')]
    public function report(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $report = new Report();
        $report->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $report->setUser($this->getUser());
        $report->setRead(false);

        //Classic form because id must be handwritten (not selected)
        if ($request->isMethod('POST')) {
            $repository = $entityManager->getRepository(Resource::class);
            $resource = $repository->find($request->request->get('tag'));

            if (! $resource){ // Check if the resource exists
                $this->addFlash('error', 'La ressource n\'existe pas');
                return $this->redirectToRoute('app_report_reportAliment');
            }

            $report->setResource($resource);
            $report->setDescription($request->request->get('description'));

            $entityManager->persist($report);
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a bien été enregistré');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('report/report.html.twig');
    }
}
