<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatchResult;
use Cspray\HttpClientTestInterceptor\Unit\MatcherResultAssertion;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\StrictHeadersMatch
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\MatchResult
 */
final class HeadersMatcherTest extends TestCase {

    public function testEmptyHeadersMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://www.example.com');
            // make sure this is explicitly set for the test
            $request->setHeaders([]);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setHeaders([]);

        $results = Matcher::Headers->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Headers->getStrategy(), 'Fixture and Request headers strictly match');
    }

    public function testSingleHeaderDoNotMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders(['Accept' => 'application/json']);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', 'text/plain');

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('Accept: application/json', 'Accept: text/plain');

        $expectedLog = <<<TEXT
Fixture and Request headers do not strictly match!

$expectedDiff
TEXT;
        $results = Matcher::Headers->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Headers->getStrategy(), $expectedLog);
    }

    public function testSingleHeaderDoesMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders(['Accept' => 'text/plain']);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', 'text/plain');

        $results = Matcher::Headers->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Headers->getStrategy(), 'Fixture and Request headers strictly match');
    }

    public function testSingleHeaderMultipleValuesNotOrderDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders(['Accept' => ['text/plain', 'text/html']]);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', ['text/html', 'text/plain']);

        $results = Matcher::Headers->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Headers->getStrategy(), 'Fixture and Request headers strictly match');
    }

    public function testMultipleHeaderMultipleValuesNotOrderDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders([
                'Accept' => ['text/plain', 'text/html'],
                'Custom' => ['foo', 'bar']
            ]);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', ['text/html', 'text/plain']);
        $request->setHeader('Custom', ['bar', 'foo']);

        $results = Matcher::Headers->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Headers->getStrategy(), 'Fixture and Request headers strictly match');
    }

    public function testMultipleHeaderKeysNotOrderDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders([
                'Accept' => ['text/plain', 'text/html'],
                'Custom' => ['foo', 'bar']
            ]);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Custom', ['bar', 'foo']);
        $request->setHeader('Accept', ['text/html', 'text/plain']);

        $results = Matcher::Headers->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Headers->getStrategy(), 'Fixture and Request headers strictly match');
    }
}