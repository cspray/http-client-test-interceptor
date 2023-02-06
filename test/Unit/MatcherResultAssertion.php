<?php

namespace Cspray\HttpClientTestInterceptor\Unit;

use Cspray\HttpClientTestInterceptor\MatchResult;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatchStrategy;
use PHPUnit\Framework\Assert;

final class MatcherResultAssertion {

    public static function assertSuccessfulMatcher(MatchResult $results, RequestMatchStrategy $matcher, string $log) : void {
        Assert::assertTrue($results->isMatched);
        Assert::assertSame($matcher, $results->matcherStrategy);
        Assert::assertSame($log, $results->log);
    }

    public static function assertFailedMatcher(MatchResult $results, RequestMatchStrategy $matcher, string $log) : void {
        Assert::assertFalse($results->isMatched);
        Assert::assertSame($matcher, $results->matcherStrategy);
        Assert::assertSame($log, $results->log);
    }

}