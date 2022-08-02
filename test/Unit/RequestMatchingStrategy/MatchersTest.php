<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\BodyMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\HeadersMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\MethodMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\ProtocolVersionsMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\UriMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers
 */
final class MatchersTest extends TestCase {

    public function matchersStrategyProvider() : array {
        return [
            [Matchers::Body, BodyMatcher::class],
            [Matchers::Headers, HeadersMatcher::class],
            [Matchers::Method, MethodMatcher::class],
            [Matchers::ProtocolVersions, ProtocolVersionsMatcher::class],
            [Matchers::Uri, UriMatcher::class]
        ];
    }

    /**
     * @dataProvider matchersStrategyProvider
     */
    public function testMatchersBuildStrategy(Matchers $matchers, string $expectedType) : void {
        self::assertInstanceOf($expectedType, $matchers->getStrategy());
    }

}