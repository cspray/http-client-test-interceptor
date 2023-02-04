<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatcherResult;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatcherStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\CompositeMatcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\MatcherResult
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\BodyMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\ProtocolVersionsMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\StrictHeadersMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatcher
 */
final class CompositeMatcherTest extends TestCase {

    public function testAllComposedMatcherResultsIsMatched() : void {
        $fixture = StubFixture::fromRequest(new Request('https://www.example.com/some/path', 'POST'));
        $request = new Request('https://www.example.com/some/path', 'POST');

        $results = Matcher::All->getStrategy()->doesFixtureMatchRequest($fixture, $request);
        $actual = array_map(static fn(MatcherResult $result) => $result->isMatched, $results);

        self::assertCount(5, $results);
        self::assertSame([true, true, true, true, true], $actual);
    }

    public function testAllComposedMatcherResultsIsCorrectMatcherType() : void {
        $fixture = StubFixture::fromRequest(new Request('https://www.example.com/some/path', 'POST'));
        $request = new Request('https://www.example.com/some/path', 'POST');

        $results = Matcher::All->getStrategy()->doesFixtureMatchRequest($fixture, $request);
        $actual = array_map(static fn(MatcherResult $result) => $result->matcherStrategy, $results);

        self::assertCount(5, $results);
        self::assertSame([
            Matcher::Uri->getStrategy(),
            Matcher::Method->getStrategy(),
            Matcher::Headers->getStrategy(),
            Matcher::Body->getStrategy(),
            Matcher::ProtocolVersions->getStrategy()
        ], $actual);
    }

}
