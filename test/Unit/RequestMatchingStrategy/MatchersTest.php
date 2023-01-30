<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\BodyMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\HeadersMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\MethodMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\ProtocolVersionsMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\UriMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 */
final class MatchersTest extends TestCase {

    public function matchersStrategyProvider() : array {
        return [
            [Matcher::Body, BodyMatcher::class],
            [Matcher::Headers, HeadersMatcher::class],
            [Matcher::Method, MethodMatcher::class],
            [Matcher::ProtocolVersions, ProtocolVersionsMatcher::class],
            [Matcher::Uri, UriMatcher::class]
        ];
    }

    /**
     * @dataProvider matchersStrategyProvider
     */
    public function testMatchersBuildStrategy(Matcher $matchers, string $expectedType) : void {
        self::assertInstanceOf($expectedType, $matchers->getStrategy());
    }

}