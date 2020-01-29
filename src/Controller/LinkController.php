<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\Exceptions\LinkNotFoundException;
use App\Services\LinksService;
use App\Services\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер ссылок.
 *
 * @package App\Controller
 */
class LinkController extends AbstractController
{
    /**
     * @var LinksService Сервис ссылок.
     */
    private $linkService;

    /**
     * @var UserService Сервис пользователей.
     */
    private $userService;

    /**
     * Конструктор класса.
     *
     * @param LinksService $linkService Сервис ссылок.
     * @param UserService $userService Сервис пользователей.
     */
    public function __construct(LinksService $linkService, UserService $userService)
    {
        $this->linkService = $linkService;
        $this->userService = $userService;
    }

    /**
     * Создание короткой ссылки.
     *
     * @param Request $request Запрос.
     *
     * @return JsonResponse
     *
     * @Route("/api/link", name="create_link",  methods={"POST"})
     */
    public function create(Request $request)
    {
        $url = $request->request->get('url');
        $category = $request->request->get('category');

        if (!isset($url)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `url`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        } elseif (!isset($category)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `category`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $newLink = $this->linkService->create($url, $category);
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => 'Короткаяссылка успешно создана.',
            'data' => [
                'id' => $newLink->getId(),
                'shortUrl' => $newLink->getShortUrl()
            ]
        ]);
    }

    /**
     * Редактирование информации о ссылке.
     *
     * @param Request $request Запрос.
     * @param int $id ИД ссылки.
     *
     * @return Response
     *
     * @Route("/api/link/{id}", name="edit_link",  methods={"PUT"})
     */
    public function edit(Request $request, int $id)
    {
        $url = $request->request->get('url');
        $category = $request->request->get('category');

        if (!isset($id)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `id`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        } elseif (!isset($url)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `url`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        } elseif (!isset($category)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `category`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->linkService->update($id, $url, $category);
        } catch (LinkNotFoundException $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => 'Короткая ссылка успешно обновлена.'
        ]);
    }

    /**
     * Удаление короткой ссылки.
     *
     * @param int $id ИД ссылки.
     *
     * @return Response
     *
     * @Route("/api/link/{id}", name="delete_link", methods={"DELETE"})
     */
    public function delete(int $id)
    {
        if (!isset($id)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `id`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->linkService->delete($id);
        } catch (LinkNotFoundException $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => 'Короткая ссылка успешно удалена.'
        ]);
    }

    /**
     * Переход по короткой ссылке.
     *
     * @param string $code Код короткой ссылки.
     *
     * @return Response
     *
     * @Route("/{code}", name="go_to_link", methods={"GET"})
     */
    public function go(string $code)
    {
        if (!isset($code)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `code`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $link = $this->linkService->getByCode($code);
            $this->linkService->visit($link);
        } catch (LinkNotFoundException $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->redirect($link->getUrl());
    }

    /**
     * Получение инфомации о ссылке по её ИД.
     *
     * @param int $id ИД ссылки.
     *
     * @return JsonResponse
     *
     * @Route("/api/link/{id}", name="get_link",  methods={"GET"})
     */
    public function getById(int $id)
    {
        if (!isset($id)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `id`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $link = $this->linkService->getById($id);
        } catch (LinkNotFoundException $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json($link);
    }

    /**
     * Получение инфомации о ссылках в заданной категории.
     *
     * @param string $category Категория ссылки.
     *
     * @return JsonResponse
     *
     * @Route("/api/links/category/{category}", name="get_links_with_category",  methods={"GET"})
     */
    public function getByCategory(string $category)
    {
        if (!isset($category)) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Не задан обязательный параметр `category`.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $links = $this->linkService->getByCategory($category);
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => '',
            'data' => [
                'links' => $links
            ]
        ]);
    }

    /**
     * Получение инфомации о всех ссылках.
     *
     * @return JsonResponse
     *
     * @Route("/api/links/all", name="get_all_links",  methods={"GET"})
     */
    public function getAll()
    {
        try {
            $links = $this->linkService->getAll();
        } catch (Exception $e) {
            return $this->json(
                [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => '',
            'data' => [
                'links' => $links
            ]
        ]);
    }
}
