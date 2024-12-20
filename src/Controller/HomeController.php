<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Import manquant
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // Le bon namespace pour les annotations
use App\Repository\RobotRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;




class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    public function index(RobotRepository $robotRepository, Request $request): Response
    {
        // Récupère la page actuelle via le paramètre 'page' (par défaut 1)
        $page = $request->query->getInt('page', 1);

        // Nombre d'éléments par page
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Récupère les robots paginés via Doctrine
        $query = $robotRepository->createQueryBuilder('r')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, true);
        $totalRobots = count($paginator); // Total de robots pour le calcul de la pagination
        $totalPages = ceil($totalRobots / $limit);

        return $this->render('./home/index.html.twig', [
            'robots' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}