<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher;
use Cspray\HttpClientTestInterceptor\Unit\MatcherResultAssertion;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\MatcherResult
 */
final class MethodMatcherTest extends TestCase {

    public function testMethodMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', 'PUT'));
        $request = new Request('http://not.example.com', 'PUT');

        $results = Matcher::Method->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher($results, Matcher::Method->getStrategy(), 'Fixture and Request method match');
    }

    public function testMethodDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', 'GET'));
        $request = new Request('http://not.example.com', 'PUT');

        $results = Matcher::Method->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('GET', 'PUT');

        $expectedLog = <<<TEXT
Fixture and Request method do not match!

$expectedDiff
TEXT;

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Method->getStrategy(), $expectedLog);
    }


}