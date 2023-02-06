<?php

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatchStrategy;

final class MatchResult {

    public function __construct(
        public readonly bool                 $isMatched,
        public readonly RequestMatchStrategy $matcherStrategy,
        public readonly string               $log
    ) {}

}
