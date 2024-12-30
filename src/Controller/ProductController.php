<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Repository\RobotRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/robot/{id}', name: 'product_show', requirements: ['id' => '\d+'])]
    public function show(int $id, RobotRepository $robotRepository): Response
    {
        $robot = $robotRepository->find($id);

        if (!$robot) {
            throw $this->createNotFoundException('Le robot demandé n\'existe pas.');
        }

        $cart = $this->getUser() ? $this->getUser()->getCarts()->first() : null;

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            // Si vous voulez le sauvegarder immédiatement dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cart);
            $entityManager->flush();
        }


        return $this->render('product/index.html.twig', [
            'robot' => $robot,
            'cart' => $cart,
        ]);
    }
}
