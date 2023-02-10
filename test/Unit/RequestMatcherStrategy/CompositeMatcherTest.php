<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\MethodMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\ProtocolVersionMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\StrictHeadersMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\UriMatcherStrategy
 */
final class CompositeMatcherTest extends TestCase {

    public function testAllComposedMatcherResultsIsMatched() : void {
        $fixture = StubFixture::fromRequest(new Request('https://www.example.com/some/path', 'POST'));
        $request = new Request('https://www.example.com/some/path', 'POST');

        $results = Matcher::All->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        self::assertTrue($results->isMatched);
    }

    public function testAllComposedMatcherResultsIsCorrectMatcherType() : void {
        $fixture = StubFixture::fromRequest(new Request('https://www.example.com/some/path', 'POST'));
        $request = new Request('https://www.example.com/some/path', 'POST');

        $results = Matcher::All->getStrategy()->doesFixtureMatchRequest($fixture, $request);
        $strategy = $results->matcherStrategy;

        self::assertInstanceOf(CompositeMatcherStrategy::class, $strategy);

        self::assertSame([
            Matcher::Uri->getStrategy(),
            Matcher::Method->getStrategy(),
            Matcher::Headers->getStrategy(),
            Matcher::Body->getStrategy(),
            Matcher::ProtocolVersions->getStrategy()
        ], $strategy->getStrategies());
    }

    public function testAllComposedMatcherResultsIsCorrectLog() : void {
        $fixture = StubFixture::fromRequest(new Request('https://www.example.com/some/path', 'POST'));
        $request = new Request('https://www.example.com/some/path', 'POST');

        $results = Matcher::All->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        $expectedLog = <<<TEXT
Fixture and Request URI match
Fixture and Request method match
Fixture and Request headers strictly match
Fixture and Request body match
Fixture and Request protocol versions match
TEXT;

        self::assertSame($expectedLog, $results->log);

    }

}
