<?php

use App\Services\ShorterService;
use PHPUnit\Framework\TestCase;

/**
 * Тест сервиса сокращения ссылок.
 */
final class ShorterServiceTest extends TestCase
{
    /**
     * Тест исключения при ИД ссылки < 1.
     */
    public function testIdLessZeroException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ИД ссылки должен быть больше 0.');
        ShorterService::encode(0);
    }

    /**
     * Тест кодирования ссылки.
     *
     * @param int $id ИД ссылки.
     * @param string $code Код ссылки.
     *
     * @dataProvider shorterProvider
     */
    public function testEncode(int $id, string $code): void
    {
        $this->assertSame(ShorterService::encode($id), $code);
    }

    /**
     * Тест декодирования ссылки.
     *
     * @param int $id ИД ссылки.
     * @param string $code КОд ссылки.
     *
     * @dataProvider shorterProvider
     */
    public function testDecode(int $id, string $code): void
    {
        $this->assertSame(ShorterService::decode($code), $id);
    }

    /**
     * Провайдер данных для тестов.
     *
     * @return array
     */
    public function shorterProvider(): array
    {
        return [
            [1, 'a'],
            [12312, 'clJ'],
            [13878455, '5nzc'],
            [123428776556, 'bjShIlN'],
            [67465334, 'dIdXH'],
        ];
    }
}
