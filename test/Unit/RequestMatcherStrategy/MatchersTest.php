<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\BodyMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\HeadersMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\ProtocolVersionsMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatcher;
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