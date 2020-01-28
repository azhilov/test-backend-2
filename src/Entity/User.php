<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * Пользователь.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @var int ИД пользователя.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string Имя пользователя.
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @var string Хэш пароля пользователя.
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var Link[] Ссылки пользователя.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Link", mappedBy="user", orphanRemoval=true)
     */
    private $links;

    /**
     * Конструктор класса.
     *
     * @param string $username Имя пользователя.
     */
    public function __construct(string $username)
    {
        $this->links = new ArrayCollection();
        $this->username = $username;
    }

    /**
     * Возвращает ИД пользователя.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * Возвращает список ссылок пользователя.
     *
     * @return Collection
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }
}
