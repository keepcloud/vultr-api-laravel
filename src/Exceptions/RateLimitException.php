<?php

namespace KeepCloud\Vultr\Exceptions;

use Illuminate\Http\Client\Response;
use Throwable;

class RateLimitException extends VultrException
{
    /**
     * Seconds to wait before retrying, from the Retry-After header.
     */
    public readonly ?int $retryAfter;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        ?Response $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);

        $header = $response?->header('Retry-After');
        $this->retryAfter = is_numeric($header) ? (int) $header : null;
    }
}
