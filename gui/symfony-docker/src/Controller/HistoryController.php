<?php

namespace App\Controller;


use App\Repository\UserResearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history/{page}', name: 'app_history')]
    public function history(UserResearchRepository $UserResearchRepository,
                            $page): Response
    {
        $user = $this->getUser();
        $numberResearch = count($UserResearchRepository->findBy(['User' => $user]));
        $history = $UserResearchRepository->findBy(['User' => $user], ['date'=> 'DESC'] , 10 , ($page * 10) - 10);
        $numberPage = ceil($numberResearch / 10);
        return $this->render('history/history.html.twig', [
            'history' => $history,
            'numberPage' => $numberPage,
            'page' => $page
        ]);

    }
}
