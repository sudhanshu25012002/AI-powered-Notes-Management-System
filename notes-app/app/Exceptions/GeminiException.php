<?php

namespace App\Exceptions;

use RuntimeException;

class GeminiException extends RuntimeException
{
    /**
     * Create a new Gemini exception.
     *
     * @param string $message  Human-readable error description
     * @param int    $code     HTTP status code from Gemini API (if applicable)
     */
    public function __construct(string $message = 'Gemini API request failed.', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
