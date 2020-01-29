<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use App\Services\Exceptions\LinkNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;

/**
 * Сервис коротких ссылок.
 *
 * @package App\Services
 */
final class LinksService
{
    /**
     * @var EntityManagerInterface Entity manager.
     */
    private $entityManager;
    /**
     * @var RandomStringsService Сервис генерации случайных строк.
     */
    private $randomStringsService;
    /**
     * @var RandomStringsService Репозиторий коротких ссылок.
     */
    private $linkRepository;
    /**
     * @var UserService Сервис пользователей.
     */
    private $userService;

    /**
     * Конструктор класса.
     *
     * @param EntityManagerInterface $entityManager Entity manager.
     * @param RandomStringsService $randomStringsService Сервис генерации случайных строк.
     * @param UserService $userService Сервис пользователей.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RandomStringsService $randomStringsService,
        UserService $userService
    )
    {
        $this->entityManager = $entityManager;
        $this->randomStringsService = $randomStringsService;
        $this->linkRepository = $entityManager->getRepository('App\Entity\Link');
        $this->userService = $userService;
    }

    /**
     * Формирует короткую ссылку.
     *
     * @param string $code Код короткой ссылки.
     *
     * @return string
     */
    private function makeShortUrl(string $code)
    {
        return sprintf(
            "%s://%s/%s",
            !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $code
        );
    }

    /**
     * Создаёт короткую ссылку.
     *
     * @param string $url Адрес полной ссылки.
     * @param string $category Категория короткой ссылки.
     *
     * @return Link Короткая ссылка.
     *
     * @throws InvalidArgumentException
     * @throws InvalidTokenException
     * @throws Exception
     */
    public function create(string $url, string $category): Link
    {
        if (empty($url)){
            throw new InvalidArgumentException('Не задан обязательный параметр `url`.');
        } elseif (empty($category)) {
            throw new InvalidArgumentException('Не задан обязательный параметр `category`.');
        }

        $randomString = $this->randomStringsService->make();
        $currentUser = $this->userService->getCurrentUser();
        $link = new Link();
        $link
            ->setCategory($category)
            ->setUpdatedAt(new DateTime())
            ->setUrl($url)
            ->setShortUrl($this->makeShortUrl($randomString))
            ->setCode($randomString)
            ->setUser($currentUser);
        $this->entityManager->persist($link);
        $this->entityManager->flush();

        return $link;
    }

    /**
     * Обновляет информацию о короткой ссылке.
     *
     * @param int $id ИД короткой ссылки.
     * @param string $url Адрес полной ссылки.
     * @param string $category Категория короткой ссылки.
     *
     * @return void
     *
     * @throws LinkNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidTokenException
     */
    public function update(int $id, string $url, string $category): void
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Не задан обязательный параметр `id`.');
        }elseif (empty($url)){
            throw new InvalidArgumentException('Не задан обязательный параметр `url`.');
        } elseif (empty($category)) {
            throw new InvalidArgumentException('Не задан обязательный параметр `category`.');
        }

        $link = $this->getById($id);
        $link
            ->setCategory($category)
            ->setUpdatedAt(new DateTime())
            ->setUrl($url);
        $this->entityManager->persist($link);
        $this->entityManager->flush();
    }

    /**
     * Удаление короткой сслыки.
     *
     * @param int $id ИД короткой ссылки.
     *
     * @return void
     *
     * @throws LinkNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidTokenException
     */
    public function delete(int $id): void
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Не задан обязательный параметр `id`.');
        }

        $link = $this->getById($id);
        $this->entityManager->remove($link);
        $this->entityManager->flush();
    }

    /**
     * Получение информации о ссылке по её ИД.
     *
     * @param int $id ИД ссылки.
     *
     * @return Link Информация о ссылке.
     *
     * @throws LinkNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidTokenException
     */
    public function getById(int $id): Link
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Не задан обязательный параметр `id`.');
        }

        $currentUser = $this->userService->getCurrentUser();
        $link = $this->linkRepository->findOneBy(['id' => $id, 'user' => $currentUser->getId()]);

        if (!$link) {
            throw new LinkNotFoundException("Ссылка с ИД равным $id не найдена.");
        }

        return $link;
    }

    /**
     * Получение информации о ссылке по её коду.
     *
     * @param string $code Код ссылки.
     *
     * @return Link Информация о ссылке.
     *
     * @throws LinkNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByCode(string $code): Link
    {
        if (empty($code)) {
            throw new InvalidArgumentException('Не задан обязательный параметр `code`.');
        }

        $link = $this->linkRepository->findOneBy(['code' => $code]);

        if (!$link) {
            throw new LinkNotFoundException("Ссылка с кодом равным `$code` не найдена.");
        }

        return $link;
    }

    /**
     * Посещение ссылки.
     *
     * @param Link $link Ссылка.
     *
     * @return void
     *
     * @throws Exception;
     */
    public function visit(Link $link): void
    {
        $link
            ->incCounter()
            ->setUpdatedAt(new DateTime());
        $this->entityManager->persist($link);
        $this->entityManager->flush();
    }

    /**
     * Возвращает весь список ссылок пользователя.
     *
     * @return Link[]
     *
     * @throws InvalidTokenException
     */
    public function getAll(): array
    {
        $currentUser = $this->userService->getCurrentUser();

        return $this->linkRepository->findBy(['user' => $currentUser->getId()]);
    }

    /**
     * Получение информации о ссылках определённой категории.
     *
     * @param string $category Категория ссылки.
     *
     * @return Link[] Информация о ссылках.
     *
     * @throws InvalidArgumentException
     * @throws InvalidTokenException
     */
    public function getByCategory(string $category): array
    {
        if (empty($category)) {
            throw new InvalidArgumentException("Ошибка получения информации о ссылке. Не задан обязательный параметр `category`.");
        }

        $currentUser = $this->userService->getCurrentUser();
        return $this->linkRepository->findBy(['category' => $category, 'user' => $currentUser->getId()]);
    }
}
