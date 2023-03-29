<?php

namespace Cspray\HttpClientTestInterceptor\HttpMock;

use Amp\ByteStream\ReadableBuffer;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Http\HttpStatus;
use League\Uri\Http;

final class MockResponse {

    public static function fromBody(string $body) : Response {
        return new Response(
            '1.1',
            HttpStatus::OK,
            null,
            [],
            new ReadableBuffer($body),
            new Request(Http::createFromString('http://placeholder.example.com'))
        );
    }

}
