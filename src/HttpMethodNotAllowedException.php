<?php declare(strict_types=1);

namespace drhino\Request;

use Exception;

/**
 * HTTP 405 Method Not Allowed Exception class.
 *
 * @see https://www.php.net/manual/en/language.exceptions.extending.php
 */
class HttpMethodNotAllowedException extends Exception
{
    private $allowedMethods = [];

    /**
     * @param Array $allowedMethods
     */
    public function __construct(Array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;

        parent::__construct('405 Method Not Allowed.', 405);
    }

    /**
     * Returns the allowed HTTP methods.
     */
    public function getAllowedMethods(): Array
    {
        return $this->allowedMethods;
    }
}
