<?php

namespace Cspray\HttpClientTestInterceptor;

use Amp\ByteStream\ReadableBuffer;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Http\Status;
use League\Uri\Http;

final class MockResponse {

    public static function fromBody(string $body) : Response {
        return new Response(
            '1.1',
            Status::OK,
            null,
            [],
            new ReadableBuffer($body),
            new Request(Http::createFromString('http://placeholder.example.com'))
        );
    }



}