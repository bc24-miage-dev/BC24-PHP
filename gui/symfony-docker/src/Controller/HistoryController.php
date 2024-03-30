<?php

namespace App\Controller;

use App\Entity\UserResearch;
use App\Repository\UserRepository;
use App\Repository\UserResearchRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history/{page}', name: 'app_history')]
    public function history(ManagerRegistry $managerRegistry,
                            UserRepository $userRepository,
                            $page): Response
    {
        $user = $this->getUser();
        $numberResearch = $userRepository->count(['User' => $user]);
        $history = $userRepository->findBy(['User' => $user], ['date'=> 'DESC'] , 10 , ($page * 10) - 10);

        $numberPage = ceil($numberResearch / 10);

        return $this->render('history/history.html.twig', [
            'history' => $history,
            'numberPage' => $numberPage,
            'page' => $page
        ]);

    }



}
