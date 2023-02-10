<?php

namespace Cspray\HttpClientTestInterceptor\Matcher;

final class MatcherStrategyResult {

    public function __construct(
        public readonly bool            $isMatched,
        public readonly MatcherStrategy $matcherStrategy,
        public readonly string          $log
    ) {}

}
