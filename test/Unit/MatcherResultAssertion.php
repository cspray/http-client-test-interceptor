<?php

namespace Cspray\HttpClientTestInterceptor\Unit;

use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use PHPUnit\Framework\Assert;

final class MatcherResultAssertion {

    public static function assertSuccessfulMatcher(MatcherStrategyResult $results, MatcherStrategy $matcher, string $log) : void {
        Assert::assertTrue($results->isMatched);
        Assert::assertSame($matcher, $results->matcherStrategy);
        Assert::assertSame($log, $results->log);
    }

    public static function assertFailedMatcher(MatcherStrategyResult $results, MatcherStrategy $matcher, string $log) : void {
        Assert::assertFalse($results->isMatched);
        Assert::assertSame($matcher, $results->matcherStrategy);
        Assert::assertSame($log, $results->log);
    }

}