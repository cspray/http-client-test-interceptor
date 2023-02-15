<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\MethodMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\ProtocolVersionMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\StrictHeadersMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\UriMatcherStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 */
final class MatchersTest extends TestCase {

    public static function matchersStrategyProvider() : array {
        return [
            [Matcher::Body, BodyMatcherStrategy::class],
            [Matcher::Headers, StrictHeadersMatcherStrategy::class],
            [Matcher::Method, MethodMatcherStrategy::class],
            [Matcher::ProtocolVersions, ProtocolVersionMatcherStrategy::class],
            [Matcher::Uri, UriMatcherStrategy::class]
        ];
    }

    /**
     * @dataProvider matchersStrategyProvider
     */
    public function testMatchersBuildStrategy(Matcher $matchers, string $expectedType) : void {
        self::assertInstanceOf($expectedType, $matchers->getStrategy());
    }

}