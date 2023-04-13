<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;

final class CompositeMatcherStrategy implements MatcherStrategy {

    /**
     * @var MatcherStrategy[]
     */
    private readonly array $strategies;

    private function __construct(
        MatcherStrategy $strategy,
        MatcherStrategy... $additionalStrategies
    ) {
        $this->strategies = array_merge([$strategy], $additionalStrategies);
    }

    public static function fromMatchers(Matcher $matcher, Matcher... $additionalMatchers) : self {
        return new self($matcher->getStrategy(), ...array_map(fn(Matcher $matcher) => $matcher->getStrategy(), $additionalMatchers));
    }

    public function getStrategies() : array {
        return $this->strategies;
    }

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $isMatched = true;
        $diffs = [];
        foreach ($this->getStrategies() as $strategy) {
            $result = $strategy->doesFixtureMatchRequest($fixture, $request);
            $isMatched = $isMatched && $result->isMatched;
            $diffs = [...$diffs, ...$result->diffs];
        }

        return new MatcherStrategyResult($isMatched, $request, $fixture, $this, $diffs);
    }
}