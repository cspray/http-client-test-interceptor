<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use PHPUnit\Framework\TestCase;

abstract class MatcherStrategyTestCase extends TestCase {

    abstract protected function subject() : MatcherStrategy;

    abstract protected function request() : Request;

    abstract protected function matchingFixture() : Fixture;

    abstract protected function nonMatchingFixture() : Fixture;

    abstract protected function expectedDiffLabel() : string;

    abstract protected function expectedNonMatchingDiff() : string;

    public function testMatchingFixtureHasResultsIsMatched() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->matchingFixture(), $this->request());

        self::assertTrue($results->isMatched);
    }

    public function testMatchingFixtureHasResultsWithCorrectRequest() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->matchingFixture(), $request = $this->request());

        self::assertSame($request, $results->request);
    }

    public function testMatchingFixtureHasResultsWithCorrectFixture() : void {
        $results = $this->subject()->doesFixtureMatchRequest($fixture = $this->matchingFixture(), $this->request());

        self::assertSame($fixture, $results->fixture);
    }

    public function testMatchingFixtureHasResultsWithCorrectStrategy() : void {
        $subject = $this->subject();
        $results = $subject->doesFixtureMatchRequest($this->matchingFixture(), $this->request());

        self::assertSame($subject, $results->matcherStrategy);
    }

    public function testMatchingFixtureHasResultsWithCorrectMatcherDiffLabel() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->matchingFixture(), $this->request());

        self::assertCount(1, $results->diffs);
        self::assertSame($this->expectedDiffLabel(), $results->diffs[0]->label);
    }

    public function testMatchingFixtureHasResultsWithCorrectMatcherDiffOutput() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->matchingFixture(), $this->request());

        self::assertCount(1, $results->diffs);
        self::assertSame('', $results->diffs[0]->diff);
    }

    public function testNotMatchingFixtureHasResultsIsNotMatched() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->nonMatchingFixture(), $this->request());

        self::assertFalse($results->isMatched);
    }

    public function testNotMatchingFixtureHasResultsWithCorrectRequest() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->nonMatchingFixture(), $request = $this->request());

        self::assertSame($request, $results->request);
    }

    public function testNotMatchingFixtureHasResultsWithCorrectFixture() : void {
        $results = $this->subject()->doesFixtureMatchRequest($fixture = $this->nonMatchingFixture(), $this->request());

        self::assertSame($fixture, $results->fixture);
    }

    public function testNotMatchingFixtureHasResultsWithCorrectStrategy() : void {
        $subject = $this->subject();
        $results = $subject->doesFixtureMatchRequest($this->nonMatchingFixture(), $this->request());

        self::assertSame($subject, $results->matcherStrategy);
    }

    public function testNotMatchingFixtureHasResultsWithCorrectMatcherDiffLabel() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->nonMatchingFixture(), $this->request());

        self::assertCount(1, $results->diffs);
        self::assertSame($this->expectedDiffLabel(), $results->diffs[0]->label);
    }

    public function testNotMatchingFixtureHasResultsWithCorrectMatcherDiffOutput() : void {
        $results = $this->subject()->doesFixtureMatchRequest($this->nonMatchingFixture(), $this->request());

        self::assertCount(1, $results->diffs);
        self::assertSame($this->expectedNonMatchingDiff(), $results->diffs[0]->diff);
    }

}
