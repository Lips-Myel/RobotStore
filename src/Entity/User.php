<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Pour l'encodeur de mot de passe

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    // Ajout d'une propriété pour gérer le mot de passe en clair
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    private ?string $role = 'ROLE_USER'; // Définir un rôle par défaut

    public function __construct()
    {
        // Initialisation, vous pouvez personnaliser si nécessaire
    }

    public function getRoles(): array
    {
        // Retourner le rôle en tableau, y compris ROLE_USER comme valeur par défaut
        return array_merge(['ROLE_USER'], explode(',', $this->role)); 
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // Effacer les informations sensibles (si besoin)
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    // Getter et Setter pour le mot de passe
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    // Pour gérer le mot de passe en clair
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    // Méthode pour encoder le mot de passe
    public function setEncodedPassword(UserPasswordHasherInterface $passwordHasher): void
    {
        if ($this->plainPassword) {
            // Encoder le mot de passe
            $this->password = $passwordHasher->hashPassword($this, $this->plainPassword);
            $this->plainPassword = null; // Effacer le mot de passe en clair après l'encodage
        }
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }
}
