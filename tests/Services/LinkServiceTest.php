<?php

use App\Entity\Link;
use App\Services\Exceptions\LinkNotFoundException;
use App\Services\LinksService;
use App\Services\RandomStringsService;
use App\Services\UserService;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Тест сервиса пользователей.
 */
final class LinkServiceTest extends KernelTestCase
{
    /**
     * @var LinksService Сервис ссылок.
     */
    private static $service;

    /**
     * @var EntityManager Entity manager.
     */
    private static $entityManager;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $container = self::bootKernel()->getContainer();
        self::$entityManager = $container->get('doctrine')->getManager();
        $encoder = new NativePasswordEncoder(null, null, PASSWORD_BCRYPT_DEFAULT_COST, PASSWORD_BCRYPT);
        $encoderFactory = new EncoderFactory(['App\Entity\User' => $encoder]);
        $passwordEncoder = new UserPasswordEncoder($encoderFactory);
        $tokenStorage = new TokenStorage();
        $userService = new UserService(self::$entityManager, $passwordEncoder, $tokenStorage);
        self::$service = new LinksService(self::$entityManager, new RandomStringsService(), $userService);
        $user = $userService->createUser('123', '123');
        $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');
        $tokenRaw = $jwtManager->create($user);
        $token = new JWTUserToken([], $user, $tokenRaw);
        $tokenStorage->setToken($token);
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
    }

    /**
     * Тест создания ссылки.
     *
     * @return Link
     *
     * @throws Exception
     */
    public function testCreateLink()
    {
        $link = self::$service->create('http://rambler.ru', 'search');
        $this->assertInstanceOf('App\Entity\Link', $link);

        return $link;
    }

    /**
     * Тест создания ссылки.
     *
     * @param Link $link Ссылка.
     *
     * @return Link
     *
     * @throws LinkNotFoundException
     *
     * @depends testCreateLink
     */
    public function testUpdateLink(Link $link)
    {
        self::$service->update($link->getId(), 'https://www.youtube.com', 'video');
        $updatedLink = self::$service->getById($link->getId());
        $this->assertEquals('https://www.youtube.com', $updatedLink->getUrl());
        $this->assertEquals('video', $updatedLink->getCategory());

        return $updatedLink;
    }

    /**
     * Тест получения информации о ссылки по её ИД.
     *
     * @param Link $link Ссылка.
     *
     * @throws LinkNotFoundException
     *
     * @depends testUpdateLink
     */
    public function testGetLinkById(Link $link)
    {
        $finedLink = self::$service->getById($link->getId());
        $this->assertInstanceOf('App\Entity\Link', $finedLink);
    }

    /**
     * Тест получения информации о ссылки по её коду.
     *
     * @param Link $link Ссылка.
     *
     * @throws LinkNotFoundException
     *
     * @depends testUpdateLink
     */
    public function testGetLinkByCode(Link $link)
    {
        $finedLink = self::$service->getByCode($link->getCode());
        $this->assertInstanceOf('App\Entity\Link', $finedLink);
    }

    /**
     * Тест увеличения счётчика посещений.
     *
     * @param Link $link Ссылка.
     *
     * @throws Exception
     *
     * @depends testUpdateLink
     */
    public function testVisitLink(Link $link)
    {
        $before = $link->getCounter();
        self::$service->visit($link);
        $this->assertIsInt($before);
        $this->assertEquals($before + 1, $link->getCounter());
    }

    /**
     * Тест получения информации обо всех ссылках пользователя.
     *
     * @param Link $link Ссылка.
     *
     * @throws Exception
     *
     * @depends testUpdateLink
     */
    public function testGetAllLinks(Link $link)
    {
        $links = self::$service->getAll();
        $this->assertIsArray($links);
        $this->assertEquals($links, [$link]);
        $this->assertCount(1, $links);
    }

    /**
     * Тест получения информации обо всех ссылках пользователя в заданной категории.
     *
     * @param Link $link Ссылка.
     *
     * @throws Exception
     *
     * @depends testUpdateLink
     */
    public function testGetAllLinksByCategory(Link $link)
    {
        $links = self::$service->getByCategory('video');
        $this->assertIsArray($links);
        $this->assertEquals($links, [$link]);
        $this->assertCount(1, $links);
        $this->assertEquals('video', $links[0]->getCategory());
    }

    /**
     * Тест удаления ссылки.
     *
     * @param Link $link Ссылка.
     *
     * @throws LinkNotFoundException
     *
     * @depends testUpdateLink
     */
    public function testDeleteLink(Link $link)
    {
        $linkId = $link->getId();
        self::$service->delete($linkId);
        $this->expectException(LinkNotFoundException::class);
        $this->expectExceptionMessage("Ссылка с ИД равным $linkId не найдена.");
        self::$service->getById($linkId);
    }

    /**
     * Тест исключения при передаче пустого адреса ссылки в метод создания.
     *
     * @throws Exception
     */
    public function testEmptyUrlInCreateLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `url`.');
        self::$service->create('', 'test');
    }

    /**
     * Тест исключения при передаче пустой категории ссылки в метод создания.
     *
     * @throws Exception
     */
    public function testEmptyCategoryInCreateLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `category`.');
        self::$service->create('test', '');
    }

    /**
     * Тест исключения при передаче пустого адреса ссылки в метод обновления.
     *
     * @throws Exception
     */
    public function testEmptyUrlInUpdateLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `url`.');
        self::$service->update(1, '', 'test');
    }

    /**
     * Тест исключения при передаче пустой категории ссылки в метод обновления.
     *
     * @throws Exception
     */
    public function testEmptyCategoryInUpdateLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `category`.');
        self::$service->update(1, 'test', '');
    }

    /**
     * Тест исключения при передаче пустого ИД ссылки в метод обновления.
     *
     * @throws Exception
     */
    public function testEmptyIdInUpdateLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `id`.');
        self::$service->update(0, 'test', 'test');
    }

    /**
     * Тест исключения при передаче пустого ИД ссылки в метод удаления.
     *
     * @throws Exception
     */
    public function testEmptyIdInDeleteLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `id`.');
        self::$service->delete(0);
    }

    /**
     * Тест исключения при передаче пустого ИД ссылки в метод получения информации о ссылке по её ИД.
     *
     * @throws Exception
     */
    public function testEmptyIdInGetByIdLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `id`.');
        self::$service->getById(0);
    }

    /**
     * Тест исключения при неудачном поиске ссылки по её ИД.
     *
     * @throws Exception
     */
    public function testLinkNotFoundExceptionInGetByIdLink()
    {
        $this->expectException(LinkNotFoundException::class);
        $this->expectExceptionMessage("Ссылка с ИД равным 100 не найдена.");
        self::$service->getById(100);
    }

    /**
     * Тест исключения при передаче пустого кода ссылки в метод получения информации о ссылке по её коду.
     *
     * @throws Exception
     */
    public function testEmptyCodeInGetByCodeLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `code`.');
        self::$service->getByCode('');
    }

    /**
     * Тест исключения при неудачном поиске ссылки по её коду.
     *
     * @throws Exception
     */
    public function testLinkNotFoundExceptionInGetByCodeLink()
    {
        $this->expectException(LinkNotFoundException::class);
        $this->expectExceptionMessage("Ссылка с кодом равным `test` не найдена.");
        self::$service->getByCode('test');
    }

    /**
     * Тест исключения при передаче пустой категории ссылки в метод получения информации о ссылках в заданной категории.
     *
     * @throws Exception
     */
    public function testEmptyCategoryInGetByCategoryLinkException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не задан обязательный параметр `category`.');
        self::$service->getByCategory('');
    }

    /**
     * @inheritDoc
     *
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$entityManager->getConnection()->exec("DELETE FROM links WHERE category = 'video';");
        self::$entityManager->getConnection()->exec("DELETE FROM user WHERE username = '123';");
        self::$entityManager->close();
        self::$entityManager = null;
        self::$service = null;
    }
}
