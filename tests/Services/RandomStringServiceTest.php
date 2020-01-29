<?php

use App\Services\RandomStringsService;
use PHPUnit\Framework\TestCase;

/**
 * Тест сервиса генерации случайных строк.
 */
final class RandomStringServiceTest extends TestCase
{
    /**
     * @var RandomStringsService Сервис генерации случаных строк.
     */
    private $service;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->service = new RandomStringsService();;
    }

    /**
     * Тест длинны генерируемой строки.
     */
    public function testRandomStringLength(): void
    {
        $this->assertEquals('10', strlen($this->service->make(10)));
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->service = null;
    }
}
