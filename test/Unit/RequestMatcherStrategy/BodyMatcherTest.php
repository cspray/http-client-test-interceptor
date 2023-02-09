<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Body\StringBody;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Unit\MatcherResultAssertion;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatch
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 */
final class BodyMatcherTest extends TestCase {

    public function testBodyMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', body: 'The request body'));
        $request = new Request('http://not.example.com', body: new StringBody('The request body'));

        $results = Matcher::Body->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        MatcherResultAssertion::assertSuccessfulMatcher(
            $results,
            Matcher::Body->getStrategy(),
            'Fixture and Request body match'
        );
    }

    public function testBodyDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', body: 'A different request body'));
        $request = new Request('http://not.example.com', body: new StringBody('The request body'));

        $results = Matcher::Body->getStrategy()->doesFixtureMatchRequest($fixture, $request);

        $expectedDiff = (new Differ(new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)))
            ->diff('A different request body', 'The request body');

        $expectedLog = <<<TEXT
Fixture and Request body do not match!

{$expectedDiff}
TEXT;

        MatcherResultAssertion::assertFailedMatcher($results, Matcher::Body->getStrategy(), $expectedLog);
    }

}