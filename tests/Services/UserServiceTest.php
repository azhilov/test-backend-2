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
    private static $service;

    /**
     * @var EntityManager Entity manager.
     */
    private static $entityManager;

    /**
     * @var TokenStorage Хранилище токенов.
     */
    private static $tokenStorage;

    /**
     * @var ContainerInterface DI контэйнер.
     */
    private static $kernelContainer;

    /**
     * @var UserPasswordEncoder Энкодер паролей.
     */
    private static $passwordEncoder;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $kernel = self::bootKernel();
        self::$kernelContainer = $kernel->getContainer();
        self::$entityManager = self::$kernelContainer->get('doctrine')->getManager();
        $encoder = new NativePasswordEncoder(null, null, PASSWORD_BCRYPT_DEFAULT_COST, PASSWORD_BCRYPT);
        $encoderFactory = new EncoderFactory(['App\Entity\User' => $encoder]);
        self::$passwordEncoder = new UserPasswordEncoder($encoderFactory);
        self::$tokenStorage = new TokenStorage();
        self::$service = new UserService(self::$entityManager, self::$passwordEncoder, self::$tokenStorage);
    }

    /**
     * Тест получения текущего пользователя.
     */
    public function testGetCurrentUser()
    {
        $user = new User('123');
        $user->setPassword(self::$passwordEncoder->encodePassword($user, '123'));
        $jwtManager = self::$kernelContainer->get('lexik_jwt_authentication.jwt_manager');
        $tokenRaw = $jwtManager->create($user);
        $token = new JWTUserToken([], $user, $tokenRaw);
        self::$tokenStorage->setToken($token);
        $tokenUser = self::$service->getCurrentUser();
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
        self::$service->createUser('', 'test');
    }

    /**
     * Тест исключения при передаче пустого пароля пользователя в метод создания.
     */
    public function testEmptyPasswordInCreateUserMethodException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось создать пользователя. Не задан обязательный параметр `password`.');
        self::$service->createUser('test', '');
    }

    /**
     * Тест исключения при передаче пустого имени пользователя в метод проверки наличия пользователя.
     */
    public function testEmptyPasswordInHasUserMethodException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось проверить наличие пользователя. Не задан обязательный параметр `username`.');
        self::$service->hasUser('');
    }

    /**
     * Тест создания пользователя.
     */
    public function testCreateUser()
    {
        $user = self::$service->createUser('123', '123');
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
        $this->assertTrue(self::$service->hasUser($user->getUsername()));
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
        self::$tokenStorage = null;
        self::$passwordEncoder = null;
        self::$service = null;
    }
}
