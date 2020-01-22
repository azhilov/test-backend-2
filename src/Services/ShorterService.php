<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Сервис сокращения ссылок.
 *
 * @package App\Services
 */
final class ShorterService
{
    /**
     * Доступные символы кодирования.
     */
    const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    /**
     * Длинна доступной последовательности символов для кодирования.
     */
    const ALPHABET_LEN = 62;

    /**
     * Кодирование ссылки. Возвращает код ИД ссылки.
     *
     * @param int $urlId ИД ссылки.
     *
     * @return string
     */
    public static function encode(int $urlId): string
    {
        if ($urlId < 1) {
            throw new InvalidArgumentException("ИД ссылки должен быть больше 0.");
        }

        $code = '';

        while ($urlId > 0) {
            $code .= self::ALPHABET[$urlId % self::ALPHABET_LEN - 1];
            $urlId = intdiv($urlId, self::ALPHABET_LEN);
        }

        return strrev($code);
    }

    /**
     * Декодирование ссылки. Возвращает закодированный ИД ссылки.
     *
     * @param string $shortCode Код ссылки.
     *
     * @return int
     */
    public static function decode(string $shortCode): int
    {
        $id = 0;
        $shortCodeLength = strlen($shortCode);

        for ($i = 0; $i < $shortCodeLength; $i++) {
            $id = $id * self::ALPHABET_LEN + strpos(self::ALPHABET, $shortCode[$i]) + 1;
        }

        return $id;
    }
}
