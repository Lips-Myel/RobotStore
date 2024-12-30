<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\RobotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/robot/{id}', name: 'product_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        RobotRepository $robotRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $robot = $robotRepository->find($id);

        if (!$robot) {
            throw $this->createNotFoundException('Le robot demandé n\'existe pas.');
        }

        $user = $this->getUser();

        // Récupérer ou créer un panier pour l'utilisateur connecté
        $cart = null;
        if ($user) {
            $cart = $user->getCarts()->first() ?: null;
        }

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);

            // Sauvegarder le panier dans la base de données
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        return $this->render('product/index.html.twig', [
            'robot' => $robot,
            'cart' => $cart,
        ]);
    }
}
