<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Ссылка.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LinkRepository")
 * @ORM\Table(name="links")
 */
class Link implements JsonSerializable
{
    /**
     * @var int ИД ссылки.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"unsigned"=true, "comment"="ИД ссылки"})
     */
    private $id;

    /**
     * @var string Адрес ссылки.
     *
     * @ORM\Column(type="string", length=1000, options={"comment"="Адрес ссылки"})
     */
    private $url;

    /**
     * @var string Код короткой ссылки.
     *
     * @ORM\Column(type="string", length=255, options={"comment"="Код ссылки"})
     */
    private $code;

    /**
     * @var string Короткая ссылка.
     *
     * @ORM\Column(type="string", length=255, options={"comment"="Короткая ссылка"})
     */
    private $shortUrl;

    /**
     * @var string Категория ссылки.
     *
     * @ORM\Column(type="string", length=255, options={"comment"="Категория ссылки"})
     */
    private $category;

    /**
     * @var int Количество переходов по ссылке.
     *
     * @ORM\Column(type="integer", options={"default"=0, "unsigned"=true, "comment"="Счётчик переходов по ссылке"})
     */
    private $counter;

    /**
     * @var DateTimeInterface Дата обновления информации о ссылке.
     *
     * @ORM\Column(type="datetime", options={"comment"="Дата обновления ссылки"})
     */
    private $updatedAt;

    /**
     * @var User Владелец ссылки.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="links")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * Конструктор класса.
     */
    public function __construct()
    {
        $this->counter = 0;
    }

    /**
     * Возвращает ИД ссылки.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает адрес сслыки.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Задаёт адрес ссылки.
     *
     * @param string $url Новый адрес ссылки.
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Возвращает адрес короткой сслыки.
     *
     * @return string|null
     */
    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    /**
     * Задаёт адрес короткой ссылки.
     *
     * @param string $shortUrl Новый адрес ссылки.
     *
     * @return $this
     */
    public function setShortUrl(string $shortUrl): self
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    /**
     * Возвращает код короткой ссылки.
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Задаёт код короткой ссылки.
     *
     * @param string $code Новый код для короткой ссылки.
     *
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Возвращает категорию ссылки.
     *
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * Задаёт категорию ссылки.
     *
     * @param string $category Новая категория ссылки.
     *
     * @return $this
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Возвращает количество переходов по ссылке.
     *
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * Возвращает дату обновления информации о ссылке.
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt->format("Y-m-d H:i:s");
    }

    /**
     * Задаёт дату обновления информации о ссылке.
     *
     * @param DateTimeInterface $updatedAt Новая дата обновления ссылки.
     *
     * @return $this
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Возвращает владельца ссылки.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Задаёт владельца ссылки.
     *
     * @param User $user Новый владелец ссылки.
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Увеличивает счётчик посещений ссылки.
     *
     * @return $this
     */
    public function incCounter(): self
    {
        $this->counter++;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'code' => $this->code,
            'shortUrl' => $this->shortUrl,
            'category' => $this->category,
            'counter' => $this->counter,
            'updatedAt' => $this->updatedAt,
            'userId' => $this->user->getId()
        ];
    }
}
