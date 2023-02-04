<?php

namespace Cspray\HttpClientTestInterceptor\Unit;

use Cspray\HttpClientTestInterceptor\MatcherResult;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatcherStrategy;
use PHPUnit\Framework\Assert;

final class MatcherResultAssertion {

    public static function assertSuccessfulMatcher(array $results, RequestMatcherStrategy $matcher, string $log) : void {
        Assert::assertCount(1, $results);
        Assert::assertContainsOnlyInstancesOf(MatcherResult::class, $results);
        Assert::assertTrue($results[0]->isMatched);
        Assert::assertSame($matcher, $results[0]->matcherStrategy);
        Assert::assertSame($log, $results[0]->log);
    }

    public static function assertFailedMatcher(array $results, RequestMatcherStrategy $matcher, string $log) : void {
        Assert::assertCount(1, $results);
        Assert::assertContainsOnlyInstancesOf(MatcherResult::class, $results);
        Assert::assertFalse($results[0]->isMatched);
        Assert::assertSame($matcher, $results[0]->matcherStrategy);
        Assert::assertSame($log, $results[0]->log);
    }

}