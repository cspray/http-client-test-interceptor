<?php

namespace Cspray\HttpClientTestInterceptor\HttpMock;

use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;

final class HttpMockerResult {

    public function __construct(
        public readonly ?Response $response,
        /**
         * @var MatcherStrategyResult
         */
        public readonly MatcherStrategyResult $matchResult
    ) {}

}