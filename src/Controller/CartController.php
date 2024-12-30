<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\RobotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    // Affiche le panier avec ses éléments
    #[Route('/cart', name: 'cart')]
    public function index(CartRepository $cartRepository, RobotRepository $robotRepository): Response
    {
        $cart = $this->getCart($cartRepository);
        $cartItems = $cart->getCartItems();

        // Transformation des CartItems en tableau incluant les robots associés
        $itemsWithRobots = array_map(function (CartItem $item) use ($robotRepository) {
            $robot = $robotRepository->find($item->getRobotId());
            return [
                'cartItem' => $item,
                'robot' => $robot,
            ];
        }, $cartItems->toArray());

        // Calcul du total du panier
        $total = array_reduce($itemsWithRobots, function ($sum, $itemWithRobot) {
            $robot = $itemWithRobot['robot'];
            $cartItem = $itemWithRobot['cartItem'];
            if ($robot) {
                return $sum + $robot->getPrice() * $cartItem->getQuantity();
            }
            return $sum;
        }, 0);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'itemsWithRobots' => $itemsWithRobots,
            'total' => $total,
        ]);
    }

    // Ajouter un robot au panier
    #[Route('/cart/add/{robotId}', name: 'cart_add', methods: ['POST', 'GET'])]
    public function add(
        int $robotId,
        RobotRepository $robotRepository,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $em
    ): Response {
        $robot = $robotRepository->find($robotId);
        if (!$robot) {
            throw $this->createNotFoundException('Robot non trouvé.');
        }

        $cart = $this->getCart($cartRepository);
        $cartItem = $cartItemRepository->findOneBy(['cart' => $cart, 'robotId' => $robotId]);

        if ($cartItem) {
            $cartItem->increaseQuantity();
        } else {
            $cartItem = (new CartItem())
                ->setCart($cart)
                ->setRobotId($robotId)
                ->setQuantity(1);
            $em->persist($cartItem);
        }

        $em->flush();

        return $this->redirectToRoute('cart');
    }

    // Supprimer un élément du panier
    #[Route('/cart/remove/{cartItemId}', name: 'cart_remove', methods: ['POST'])]
    public function remove(
        int $cartItemId,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $em
    ): Response {
        $cart = $this->getCart($cartRepository);
        $cartItem = $cartItemRepository->find($cartItemId);

        if ($cartItem) {
            $em->remove($cartItem);
            $em->flush();
        }

        return $this->redirectToRoute('cart');
    }

    // Augmenter la quantité d'un article
    #[Route('/cart/increase/{cartItemId}', name: 'cart_increase', methods: ['POST'])]
    public function increaseQuantity(
        int $cartItemId,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $em
    ): Response {
        $cart = $this->getCart($cartRepository);
        $cartItem = $cartItemRepository->find($cartItemId);

        if ($cartItem) {
            $cartItem->increaseQuantity();
            $em->flush();
        }

        return $this->redirectToRoute('cart');
    }

    // Diminuer la quantité d'un article
    #[Route('/cart/decrease/{cartItemId}', name: 'cart_decrease', methods: ['POST'])]
    public function decreaseQuantity(
        int $cartItemId,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $em
    ): Response {
        $cart = $this->getCart($cartRepository);
        $cartItem = $cartItemRepository->find($cartItemId);

        if ($cartItem) {
            if ($cartItem->getQuantity() > 1) {
                $cartItem->decreaseQuantity();
                $em->flush();
            } else {
                $this->addFlash('info', 'La quantité ne peut pas être inférieure à 1.');
            }
        }

        return $this->redirectToRoute('cart');
    }

    // Vider le panier
    #[Route('/cart/clear', name: 'cart_clear', methods: ['POST'])]
    public function clear(CartRepository $cartRepository, EntityManagerInterface $em): Response
    {
        $cart = $this->getCart($cartRepository);

        foreach ($cart->getCartItems() as $cartItem) {
            $em->remove($cartItem);
        }

        $em->flush();

        return $this->redirectToRoute('cart');
    }

    // Méthode privée pour récupérer ou créer un panier
    private function getCart(CartRepository $cartRepository): Cart
    {
        $session = $this->requestStack->getSession();
        $cartId = $session->get('cart_id');

        // Si l'utilisateur est connecté, récupérer ou créer un panier pour lui
        if ($this->getUser()) {
            $cart = $cartRepository->findOneBy(['user' => $this->getUser()]);
            if (!$cart) {
                $cart = new Cart();
                $cart->setUser($this->getUser());
                $cartRepository->save($cart, true);
            }
        } else {
            // Si l'utilisateur n'est pas connecté, utiliser le panier stocké en session
            $cart = $cartId ? $cartRepository->find($cartId) : null;
            if (!$cart) {
                $cart = new Cart();
                $cartRepository->save($cart, true);
                $session->set('cart_id', $cart->getId());
            }
        }

        return $cart;
    }
}
