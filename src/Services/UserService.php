<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Сервис пользователей.
 *
 * @package App\Services
 */
final class UserService
{
    /**
     * @var EntityManagerInterface Менеджер сущностей.
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface Кодировшик паролей.
     */
    private $passwordEncoder;
    /**
     * @var TokenStorageInterface Хранилище токенов.
     */
    private $tokenStorage;

    /**
     * Конструктор класса.
     *
     * @param EntityManagerInterface $entityManager Менеджер сущностей.
     * @param UserPasswordEncoderInterface $encoder Кодировшик паролей.
     * @param TokenStorageInterface $storage Хранилище токенов.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder,
        TokenStorageInterface $storage
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $encoder;
        $this->tokenStorage = $storage;
    }

    /**
     * Возвращает информацию о текущем пользователе.
     *
     * @return User
     *
     * @throws InvalidTokenException
     */
    public function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof TokenInterface) {
            return $token->getUser();
        } else {
            throw new InvalidTokenException();
        }
    }

    /**
     * Проверяет, если-ли пользователь с заданным именем.
     *
     * @param string $username Имя пользователя.
     *
     * @return bool
     */
    public function hasUser(string $username): bool
    {
        if (empty($username)) {
            throw new InvalidArgumentException('Не удалось проверить наличие пользователя. Не задан обязательный параметр `username`.');
        }

        $repository = $this->entityManager->getRepository('App\Entity\User');
        $user = $repository->findOneBy(['username' => $username]);

        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Создаёт пользователя.
     *
     * @param string $username Имя пользователя.
     * @param string $password Пароль пользователя.
     *
     * @return User Пользователь.
     */
    public function createUser(string $username, string $password): User
    {
        if (empty($username)) {
            throw new InvalidArgumentException('Не удалось создать пользователя. Не задан обязательный параметр `username`.');
        } elseif (empty($password)) {
            throw new InvalidArgumentException('Не удалось создать пользователя. Не задан обязательный параметр `password`.');
        }

        $user = new User($username);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
