<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Unit\MatcherResultAssertion;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\ProtocolVersionMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 */
final class ProtocolVersionsMatcherTest extends TestCase {

    public function testSingleProtocolVersionMatches() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.0']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0']);

        $results = Matcher::ProtocolVersions->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::ProtocolVersions->getStrategy(), 'Fixture and Request protocol versions match');
    }

    public function testSingleProtocolVersionDoNotMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.0']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.1']);

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff(['1.0'], ['1.1']);

        $expectedLog = <<<TEXT
Fixture and Request protocol versions do not match!

$expectedDiff
TEXT;

        $results = Matcher::ProtocolVersions->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::ProtocolVersions->getStrategy(), $expectedLog);
    }

    public function testMultipleProtocolVersionsMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.0', '2']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0', '2']);

        $results = Matcher::ProtocolVersions->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::ProtocolVersions->getStrategy(), 'Fixture and Request protocol versions match');
    }

    public function testMultipleProtocolVersionsDoNotMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.1', '2']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0', '2']);

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff(['1.1', '2'], ['1.0', '2']);

        $expectedLog = <<<TEXT
Fixture and Request protocol versions do not match!

$expectedDiff
TEXT;

        $results = Matcher::ProtocolVersions->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::ProtocolVersions->getStrategy(), $expectedLog);
    }

    public function testMultipleProtocolVersionsAreNotSortDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.1', '2']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['2', '1.1']);

        $results = Matcher::ProtocolVersions->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::ProtocolVersions->getStrategy(), 'Fixture and Request protocol versions match');
    }

}