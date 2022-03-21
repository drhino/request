<?php declare(strict_types=1);

namespace drhino\Request;

use Exception;

/**
 * HTTP 404 Not Found Exception class.
 *
 * @see https://www.php.net/manual/en/language.exceptions.extending.php
 */
class HttpNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('404 Not Found.', 404);
    }
}
