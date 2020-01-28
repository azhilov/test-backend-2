<?php

use App\Entity\User;
use App\Services\UserService;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Тест сервиса пользователей.
 */
final class UserServiceTest extends KernelTestCase
{
    /**
     * @var UserService Сервис пользователей.
     */
    private $service;
    /**
     * @var EntityManager Entity manager.
     */
    private static $entityManager;
    /**
     * @var TokenStorage Хранилище токенов.
     */
    private $tokenStorage;
    /**
     * @var ContainerInterface DI контэйнер.
     */
    private static $kernelContainer;
    /**
     * @var UserPasswordEncoder Энкодер паролей.
     */
    private $passwordEncoder;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $kernel = self::bootKernel();
        self::$kernelContainer = $kernel->getContainer();
        self::$entityManager = self::$kernelContainer->get('doctrine')->getManager();
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $encoder = new NativePasswordEncoder(null, null, PASSWORD_BCRYPT_DEFAULT_COST, PASSWORD_BCRYPT);
        $encoderFactory = new EncoderFactory(['App\Entity\User' => $encoder]);
        $this->passwordEncoder = new UserPasswordEncoder($encoderFactory);
        $this->tokenStorage = new TokenStorage();
        $this->service = new UserService(self::$entityManager, $this->passwordEncoder, $this->tokenStorage);
    }

    /**
     * Тест получения текущего пользователя.
     */
    public function testGetCurrentUser()
    {
        $user = new User('123');
        $user->setPassword($this->passwordEncoder->encodePassword($user, '123'));
        $jwtManager = self::$kernelContainer->get('lexik_jwt_authentication.jwt_manager');
        $tokenRaw = $jwtManager->create($user);
        $token = new JWTUserToken([], $user, $tokenRaw);
        $this->tokenStorage->setToken($token);
        $tokenUser = $this->service->getCurrentUser();
        $this->assertInstanceOf('App\Entity\User', $tokenUser);
        $this->assertEquals($user, $tokenUser);
    }

    /**
     * Тест исключения при передаче пустого имени пользователя в метод создания.
     */
    public function testEmptyUsernameInCreateUserMethodException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось создать пользователя. Не задан обязательный параметр `username`.');
        $this->service->createUser('', 'test');
    }

    /**
     * Тест исключения при передаче пустого пароля пользователя в метод создания.
     */
    public function testEmptyPasswordInCreateUserMethodException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось создать пользователя. Не задан обязательный параметр `password`.');
        $this->service->createUser('test', '');
    }

    /**
     * Тест исключения при передаче пустого имени пользователя в метод проверки наличия пользователя.
     */
    public function testEmptyPasswordInHasUserMethodException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось проверить наличие пользователя. Не задан обязательный параметр `username`.');
        $this->service->hasUser('');
    }

    /**
     * Тест создания пользователя.
     */
    public function testCreateUser()
    {
        $user = $this->service->createUser('123', '123');
        $this->assertInstanceOf('App\Entity\User', $user);

        return $user;
    }

    /**
     * Тест проверки наличия пользователя по его имени.
     *
     * @param User $user Пользователь.
     *
     * @depends testCreateUser
     */
    public function testHasUser(User $user)
    {
        $this->assertTrue($this->service->hasUser($user->getUsername()));
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->tokenStorage = null;
        $this->passwordEncoder = null;
        $this->service = null;
    }

    /**
     * @inheritDoc
     *
     * @throws DBALException
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$entityManager->getConnection()->exec("DELETE FROM user WHERE username = '123';");
        self::$entityManager->close();
        self::$entityManager = null;
    }
}
