<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тест контроллера пользователей.
 *
 * @package App\Tests\Controller
 */
class AuthControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser Клиент.
     */
    private $client;
    /**
     * @var EntityManager Entity Manager.
     */
    private $entityManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Тест регистрации пользователя.
     */
    public function testRegister(): void
    {
        $this->client->xmlHttpRequest(
            'POST',
            'http://127.0.0.1:8000/register',
            ['username' => '555', 'password' => '555']
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->getConnection()->exec("DELETE FROM user WHERE username = '555';");
        $this->entityManager->close();
        $this->entityManager = null;
        $this->client = null;
    }
}
