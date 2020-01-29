<?php

namespace App\Tests\Controller;

use App\Services\UserService;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Тест контроллера пользователей.
 *
 * @package App\Tests\Controller
 */
class LinkControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser Клиент.
     */
    private static $client;

    /**
     * @var EntityManager Entity Manager.
     */
    private static $entityManager;

    /**
     * @var TokenStorage Хранилище токенов.
     */
    private static $tokenStorage;

    /**
     * @var string Токен.
     */
    private static $tokenRaw;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client = static::createClient();
        $container = self::$client->getContainer();
        self::$entityManager = $container->get('doctrine')->getManager();
        $encoder = new NativePasswordEncoder(null, null, PASSWORD_BCRYPT_DEFAULT_COST, PASSWORD_BCRYPT);
        $encoderFactory = new EncoderFactory(['App\Entity\User' => $encoder]);
        $passwordEncoder = new UserPasswordEncoder($encoderFactory);
        self::$tokenStorage = new TokenStorage();
        $userService = new UserService(self::$entityManager, $passwordEncoder, self::$tokenStorage);
        $user = $userService->createUser('123', '123');
        $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');
        self::$tokenRaw = $jwtManager->create($user);
        $token = new JWTUserToken([], $user, self::$tokenRaw);
        self::$tokenStorage->setToken($token);
        $_SERVER['SERVER_NAME'] = '127.0.0.1:8000';
    }

    /**
     * Тест создания ссылки.
     *
     * @return int
     */
    public function testCreate(): int
    {
        self::$client->xmlHttpRequest(
            'POST',
            'http://127.0.0.1:8000/api/link',
            ['url' => '555', 'category' => '555'],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . self::$tokenRaw,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );
        $responseContent = json_decode(self::$client->getResponse()->getContent(), true);
        $linkId = $responseContent['data']['id'];
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        return $linkId;
    }

    /**
     * Тест обновления ссылки.
     *
     * @param int $linkId ИД ссылки.
     *
     * @depends testCreate
     */
    public function testUpdate(int $linkId): void
    {
        self::$client->xmlHttpRequest(
            'PUT',
            "http://127.0.0.1:8000/api/link/$linkId",
            ['url' => '777', 'category' => '555'],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . self::$tokenRaw,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Тест получения ссылки по ИД.
     *
     * @param int $linkId ИД ссылки.
     *
     * @return array
     *
     * @depends testCreate
     */
    public function testGetById(int $linkId): array
    {
        self::$client->xmlHttpRequest(
            'GET',
            "http://127.0.0.1:8000/api/link/$linkId",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . self::$tokenRaw
            ]
        );
        $linkData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertIsArray($linkData);

        return $linkData;
    }

    /**
     * Тест перехода по короткой ссылке.
     *
     * @param array $linkData Данные о ссылке.
     *
     * @depends testGetById
     */
    public function testGo(array $linkData): void
    {
        self::$client->xmlHttpRequest(
            'GET',
            'http://127.0.0.1:8000/' . $linkData['code'],
            [],
            [],
            []
        );
        $this->assertEquals(302, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Тест получения информации о ссылках по категории.
     *
     * @param array $linkData Данные о ссылке.
     *
     * @depends testGetById
     */
    public function testGetByCategory(array $linkData): void
    {
        self::$client->xmlHttpRequest(
            'GET',
            'http://127.0.0.1:8000/api/links/category/' . $linkData['category'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . self::$tokenRaw]
        );
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Тест получения информации о ссылках пользователя.
     */
    public function testGetAll(): void
    {
        self::$client->xmlHttpRequest(
            'GET',
            'http://127.0.0.1:8000/api/links/all',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . self::$tokenRaw]
        );
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Тест удаления ссылки.
     *
     * @param int $linkId ИД ссылки.
     *
     * @depends testCreate
     */
    public function testDelete(int $linkId): void
    {
        self::$client->xmlHttpRequest(
            'DELETE',
            "http://127.0.0.1:8000/api/link/$linkId",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . self::$tokenRaw,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @inheritDoc
     *
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$entityManager->getConnection()->exec("DELETE FROM links WHERE category = '555';");
        self::$entityManager->getConnection()->exec("DELETE FROM user WHERE username = '123';");
        self::$entityManager->close();
        self::$entityManager = null;
        self::$client = null;
        self::$tokenStorage = null;
    }
}
