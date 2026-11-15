<?php

namespace KeepCloud\Vultr\Exceptions;

use Illuminate\Http\Client\ConnectionException as HttpConnectionException;

class ConnectionException extends VultrException
{
    public static function fromHttp(HttpConnectionException $e): self
    {
        return new self($e->getMessage(), 0, $e);
    }
}
