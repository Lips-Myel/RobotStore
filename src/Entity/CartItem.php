<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use App\Repository\RobotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Robot; // Ajoutez cette ligne pour inclure l'entité Robot

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    #[ORM\Column(type: 'integer')]
    private ?int $robotId = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?int $quantity = null;

    public function __construct()
    {
        $this->quantity = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        $this->cart = $cart;

        if ($cart && !$cart->getCartItems()->contains($this)) {
            $cart->addCartItem($this);
        }

        return $this;
    }

    public function getRobotId(): ?int
    {
        return $this->robotId;
    }

    public function setRobotId(int $robotId): static
    {
        $this->robotId = $robotId;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('La quantité doit être au moins égale à 1.');
        }

        $this->quantity = $quantity;

        return $this;
    }

    public function increaseQuantity()
    {
        $this->quantity += 1;
    }

    public function decreaseQuantity(int $amount = 1): static
    {
        if ($this->quantity - $amount < 1) {
            throw new \InvalidArgumentException('La quantité ne peut pas être inférieure à 1.');
        }

        $this->quantity -= $amount;
        return $this;
    }

    // Méthode pour récupérer un objet Robot à partir de l'ID
    public function getRobot(RobotRepository $robotRepository): ?Robot
    {
        return $robotRepository->find($this->robotId);  // Récupère l'objet Robot avec l'ID
    }
}
