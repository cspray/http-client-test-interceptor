<?php

namespace Cspray\HttpClientTestInterceptor;

use Amp\Http\Client\Response;

final class HttpMockerResult {

    public function __construct(
        public readonly ?Response $response,
        /**
         * @var MatchResult
         */
        public readonly MatchResult $matchResult
    ) {}

}