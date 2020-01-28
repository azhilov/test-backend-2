<?php

namespace App\Services;

/**
 * Сервис генерации случайных строк.
 *
 * @package App\Services
 */
final class RandomStringsService
{
    /**
     * Доступные символы.
     */
    const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    /**
     * Длинна доступной последовательности символов.
     */
    const ALPHABET_LEN = 62;

    /**
     * Возвращает сгенерированную случайною строку.
     *
     * @param int $length Длинна генерируемой строки.
     *
     * @return string
     */
    public function make($length = 5) {
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomCharacter = self::ALPHABET[mt_rand(0, self::ALPHABET_LEN - 1)];
            $randomString .= $randomCharacter;
        }

        return $randomString;
    }
}
