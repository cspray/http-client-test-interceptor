<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\BodyMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\StrictHeadersMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\ProtocolVersionsMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 */
final class MatchersTest extends TestCase {

    public function matchersStrategyProvider() : array {
        return [
            [Matcher::Body, BodyMatch::class],
            [Matcher::Headers, StrictHeadersMatch::class],
            [Matcher::Method, MethodMatch::class],
            [Matcher::ProtocolVersions, ProtocolVersionsMatch::class],
            [Matcher::Uri, UriMatch::class]
        ];
    }

    /**
     * @dataProvider matchersStrategyProvider
     */
    public function testMatchersBuildStrategy(Matcher $matchers, string $expectedType) : void {
        self::assertInstanceOf($expectedType, $matchers->getStrategy());
    }

}