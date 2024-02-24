<?php

namespace App\Controller;

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
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($report);
            $entityManager->flush();
            return $this->redirectToRoute('app_index');
        }

        return $this->render('report/report.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
