<?php
// src/Controller/CartController.php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Robot;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route("/cart", name: "cart")]
    public function index(Request $request, CartItemRepository $cartItemRepository): Response
    {
        // Récupérer le panier depuis la session
        $user = $this->getUser(); // Exemple : Assurez-vous que l'utilisateur est connecté
        $cart = $this->getDoctrine()->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);  // Assurez-vous d'avoir un utilisateur associé
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        // Récupérer les éléments du panier (CartItem)
        $cartItems = $cart->getCartItems();
        
        // Calculer le total du panier
        $total = 0;
        foreach ($cartItems as $cartItem) {
            $total += $cartItem->getRobot()->getPrice() * $cartItem->getQuantity();
        }

        // Passer le panier et le total à la vue
        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route("/cart/add/{id}", name: "cart_add")]
    public function add(Request $request, $id, CartItemRepository $cartItemRepository, EntityManagerInterface $em): Response
    {
        // Récupérer le produit
        $product = $this->getDoctrine()->getRepository(Robot::class)->find($id);

        if ($product) {
            $user = $this->getUser(); // Assurez-vous que l'utilisateur est connecté
            $cart = $this->getDoctrine()->getRepository(Cart::class)->findOneBy(['user' => $user]);

            if (!$cart) {
                $cart = new Cart();
                $cart->setUser($user);
                $em->persist($cart);
                $em->flush();
            }

            // Vérifier si l'élément est déjà dans le panier
            $existingCartItem = $cartItemRepository->findOneBy(['cart' => $cart, 'robot' => $product]);

            if ($existingCartItem) {
                // Si l'élément existe déjà, on augmente la quantité
                $existingCartItem->setQuantity($existingCartItem->getQuantity() + 1);
                $em->flush();
            } else {
                // Sinon, on crée un nouvel item dans le panier
                $cartItem = new CartItem();
                $cartItem->setCart($cart);
                $cartItem->setRobot($product);
                $cartItem->setQuantity(1);  // Par défaut une seule unité
                $em->persist($cartItem);
                $em->flush();
            }
        }

        return $this->redirectToRoute('cart');
    }

    #[Route("/cart/remove/{id}", name: "cart_remove")]
    public function remove(Request $request, $id, CartItemRepository $cartItemRepository, EntityManagerInterface $em): Response
    {
        // Récupérer l'élément du panier
        $cartItem = $cartItemRepository->find($id);

        if ($cartItem) {
            $em->remove($cartItem);
            $em->flush();
        }

        return $this->redirectToRoute('cart');
    }

    #[Route("/cart/clear", name: "cart_clear")]
    public function clear(Request $request, CartItemRepository $cartItemRepository, EntityManagerInterface $em): Response
    {
        // Effacer tous les éléments du panier pour l'utilisateur
        $user = $this->getUser(); // Exemple : Assurez-vous que l'utilisateur est connecté
        $cart = $this->getDoctrine()->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if ($cart) {
            foreach ($cart->getCartItems() as $cartItem) {
                $em->remove($cartItem);
            }
            $em->flush();
        }

        return $this->redirectToRoute('cart');
    }
}
