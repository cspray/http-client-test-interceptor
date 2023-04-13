<?php

namespace Cspray\HttpClientTestInterceptor\Matcher;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

final class MatcherStrategyResult {

    /**
     * @param bool $isMatched
     * @param Request $request
     * @param Fixture $fixture
     * @param MatcherStrategy $matcherStrategy
     * @param list<MatcherDiff> $diffs
     */
    public function __construct(
        public readonly bool            $isMatched,
        public readonly Request $request,
        public readonly Fixture $fixture,
        public readonly MatcherStrategy $matcherStrategy,
        public readonly array $diffs
    ) {}

}
