<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер авторизации и аутентификации пользователей.
 *
 * @package App\Controller
 */
class AuthController extends AbstractController
{
    /**
     * @var UserService Сервис пользователей.
     */
    private UserService $userService;

    /**
     * Конструктор класса.
     *
     * @param UserService $service Сервис пользователей.
     */
    public function __construct(UserService $service)
    {
        $this->userService = $service;
    }

    /**
     * Регистрация пользователя.
     *
     * @param Request $request Запрос.
     *
     * @return JsonResponse
     *
     * @Route("/register", name="register_user",  methods={"POST"})
     */
    public function register(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (!isset($username)) {
            return $this->json(
                ['error' => 'Ошибка регистрации пользователя. Не передан обязательный параметр `username`.'],
                Response::HTTP_BAD_REQUEST
            );
        } elseif (!isset($password)) {
            return $this->json(
                ['error' => 'Ошибка регистрации пользователя. Не передан обязательный параметр `password`.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            if ($this->userService->hasUser($username)) {
                return $this->json(
                    ['error' => "Ошибка регистрации пользователя. Пользователь с именем $username уже зарегистрирован."],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $user = $this->userService->createUser($username, $password);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['newUserId' => $user->getId()]);
    }
}
