<?php

declare(strict_types=1);

namespace App\Services\Exceptions;

use Exception;

/**
 * Исключение возникающее при отсутствии информации о ссылке.
 *
 * @package App\Services\Exceptions
 */
class LinkNotFoundException extends Exception
{
}
