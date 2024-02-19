<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity("email", "username")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 25, unique: true)]
    #[Assert\Length(max: 60, maxMessage: "Le nom d'utilisateur est trop long")]
    #[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private string $username;

    #[ORM\Column(type: "string", length: 64)]
    #[Assert\Length(max: 64, maxMessage: "Le mot de passe est trop long")]
    #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide.")]
    private string $password;

    #[ORM\Column(type: "string", length: 60, unique: true)]
    #[Assert\Length(max: 60, maxMessage: "L'email est trop long")]
    #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse email.")]
    private string $email;

    #[ORM\Column(type: "string", length: 45)]
    #[Assert\NotBlank(message: "Le rôle ne peut pas être vide.")]
    #[Assert\Length(max: 45, maxMessage: "Le rôle est trop long")]
    private string $role;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }


    /**
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }


    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    /**
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    /**
     * @param string $email
     *
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }


    /**
     * @return string
     */
    public function getRole(): string
    {

        return $this->role;
    }


    /**
     * @param string $role
     *
     * @return void
     */
    public function setRole(string $role): void
    {

        $this->role = $role;
    }


    /**
     * @return array
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }


    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
    }
}
