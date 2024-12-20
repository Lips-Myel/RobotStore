<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Robot>
     */
    #[ORM\OneToMany(targetEntity: Robot::class, mappedBy: 'category')]
    private Collection $robots;

    public function __construct()
    {
        $this->robots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Robot>
     */
    public function getRobots(): Collection
    {
        return $this->robots;
    }

    public function addRobot(Robot $robot): static
    {
        if (!$this->robots->contains($robot)) {
            $this->robots->add($robot);
            $robot->setCategory($this);
        }

        return $this;
    }

    public function removeRobot(Robot $robot): static
    {
        if ($this->robots->removeElement($robot)) {
            // set the owning side to null (unless already changed)
            if ($robot->getCategory() === $this) {
                $robot->setCategory(null);
            }
        }

        return $this;
    }
}
