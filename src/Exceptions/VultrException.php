<?php

namespace KeepCloud\Vultr\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class VultrException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?Response $response = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Build the exception matching the response status.
     */
    public static function fromResponse(Response $response): self
    {
        $status = $response->status();
        $class = match (true) {
            in_array($status, [401, 403], true) => AuthenticationException::class,
            $status === 404 => NotFoundException::class,
            in_array($status, [400, 422], true) => ValidationException::class,
            $status === 429 => RateLimitException::class,
            $status >= 500 => ServerException::class,
            default => self::class,
        };

        return new $class(self::messageFrom($response), $status, null, $response);
    }

    protected static function messageFrom(Response $response): string
    {
        $data = json_decode($response->body(), true);
        $error = is_array($data) ? ($data['error'] ?? null) : null;

        return is_string($error) && $error !== '' ? $error : 'HTTP '.$response->status();
    }
}
