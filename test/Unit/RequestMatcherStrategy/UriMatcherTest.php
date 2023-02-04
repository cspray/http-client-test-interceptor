<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatcher;
use Cspray\HttpClientTestInterceptor\Unit\MatcherResultAssertion;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\MatcherResult
 */
final class UriMatcherTest extends TestCase {

    public function testCheckSchemeAndHostMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://example.com', 'POST');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckSchemeDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('https://example.com'));
        $request = new Request('http://example.com', 'POST');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('https://example.com', 'http://example.com');

        $expectedLog = <<<TEXT
Fixture and Request URI do not match!

$expectedDiff
TEXT;

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Uri->getStrategy(), $expectedLog);
    }

    public function testCheckHostDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://not.example.com');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('http://example.com', 'http://not.example.com');

        $expectedLog = <<<TEXT
Fixture and Request URI do not match!

$expectedDiff
TEXT;

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Uri->getStrategy(), $expectedLog);
    }

    public function testCheckWithMatchingSchemeHostAndPortReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com:4200'));
        $request = new Request('http://example.com:4200');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckPortDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com:81'));
        $request = new Request('http://example.com');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('http://example.com:81', 'http://example.com');

        $expectedLog = <<<TEXT
Fixture and Request URI do not match!

$expectedDiff
TEXT;

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Uri->getStrategy(), $expectedLog);
    }

    public function testCheckImplicitPortHttpReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com:80'));
        $request = new Request('http://example.com');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckMatchingSchemeHostPathReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com/my/path'));
        $request = new Request('http://example.com/my/path');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckPathDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com/my/other/path'));
        $request = new Request('http://example.com/my/path');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('http://example.com/my/other/path', 'http://example.com/my/path');

        $expectedLog = <<<TEXT
Fixture and Request URI do not match!

$expectedDiff
TEXT;

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Uri->getStrategy(), $expectedLog);
    }

    public function testCheckSchemeHostQueryParametersMatchReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com?foo=bar&bar=baz'));
        $request = new Request('http://example.com?foo=bar&bar=baz');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckQueryParametersNotOrderDependentReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com?foo=bar&bar=baz'));
        $request = new Request('http://example.com?bar=baz&foo=bar');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckingQueryParametersDifferentReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com?foo=bar&bar=bay'));
        $request = new Request('http://example.com?bar=baz&foo=bar');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('http://example.com?foo=bar&bar=bay', 'http://example.com?bar=baz&foo=bar');

        $expectedLog = <<<TEXT
Fixture and Request URI do not match!

$expectedDiff
TEXT;

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Uri->getStrategy(), $expectedLog);
    }

    public function testCheckSchemeHostFragmentMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com#fragment'));
        $request = new Request('http://example.com#fragment');

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Uri->getStrategy(), 'Fixture and Request URI match');
    }

    public function testCheckFragmentDoesNotMatch() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com#fragment'));
        $request = new Request('http://example.com#frag');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('http://example.com#fragment', 'http://example.com#frag');

        $expectedLog = <<<TEXT
Fixture and Request URI do not match!

$expectedDiff
TEXT;

        $results = Matcher::Uri->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Uri->getStrategy(), $expectedLog);
    }
}