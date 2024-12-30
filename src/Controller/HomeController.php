<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RobotRepository;
use App\Repository\CategoryRepository; // Importation du repository des catégories
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    private $requestStack;
    private $entityManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    #[Route("/", name: "home")]
    public function index(
        RobotRepository $robotRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response {
        // Récupère la page actuelle et les paramètres de tri
        $page = $request->query->getInt('page', 1);
        $sort = $request->query->get('sort', 'name'); // Critère de tri par défaut
        $categoryId = $request->query->get('category'); // ID de la catégorie sélectionnée
        $limit = 12;
        $offset = ($page - 1) * $limit;

        // Construction de la requête pour les robots
        $queryBuilder = $robotRepository->createQueryBuilder('r')
            ->leftJoin('r.category', 'c') // Jointure avec la table des catégories
            ->addSelect('c');

        if ($categoryId) {
            $queryBuilder->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        // Applique le tri
        switch ($sort) {
            case 'price_asc':
                $queryBuilder->orderBy('r.price', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('r.price', 'DESC');
                break;
            case 'name_asc':
                $queryBuilder->orderBy('r.name', 'ASC');
                break;
            case 'name_desc':
                $queryBuilder->orderBy('r.name', 'DESC');
                break;
            default:
                $queryBuilder->orderBy('r.name', 'ASC');
        }

        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
        $query = $queryBuilder->getQuery();

        // Pagination
        $paginator = new Paginator($query, true);
        $totalRobots = count($paginator);
        $totalPages = ceil($totalRobots / $limit);

        // Récupère toutes les catégories pour le filtre
        $categories = $categoryRepository->findAll();

        // Récupère ou crée un panier pour la session
        $session = $this->requestStack->getSession();
        $cartId = $session->get('cart_id');
        $cart = null;

        if ($cartId) {
            $cart = $this->entityManager->getRepository(Cart::class)->find($cartId);
        }

        if (!$cart) {
            $cart = new Cart();
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
            $session->set('cart_id', $cart->getId());
        }

        return $this->render('./home/index.html.twig', [
            'robots' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'cart' => $cart,
            'categories' => $categories, // Passe les catégories à la vue
            'selectedCategory' => $categoryId, // Passe la catégorie sélectionnée
            'sort' => $sort, // Passe le critère de tri à la vue
        ]);
    }
}
