<?php

namespace App\Controller;

use Pimcore\Model\DataObject\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    #[Route('/teams', name: 'app_team_list')]
    public function list(): Response
    {
        $teams = Team::getList();

        return $this->render('team/list.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/teams/{id}', name: 'app_team_details')]
    public function show(int $id): Response
    {
        $team = Team::getById($id);

        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }
}
