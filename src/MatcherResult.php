<?php

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatcherStrategy;

final class MatcherResult {

    public function __construct(
        public readonly bool $isMatched,
        public readonly RequestMatcherStrategy $matcherStrategy,
        public readonly string $log
    ) {}

}
