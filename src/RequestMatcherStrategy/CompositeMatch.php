<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatchResult;

final class CompositeMatch implements RequestMatchStrategy {

    /**
     * @var RequestMatchStrategy[]
     */
    private readonly array $strategies;

    private function __construct(
        RequestMatchStrategy $strategy,
        RequestMatchStrategy... $additionalStrategies
    ) {
        $this->strategies = array_merge([$strategy], $additionalStrategies);
    }

    public static function fromMatchers(Matcher $matcher, Matcher... $additionalMatchers) : self {
        return new self($matcher->getStrategy(), ...array_map(fn(Matcher $matcher) => $matcher->getStrategy(), $additionalMatchers));
    }

    public function getStrategies() : array {
        return $this->strategies;
    }

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatchResult {
        $isMatched = true;
        $log = '';
        foreach ($this->getStrategies() as $strategy) {
            $result = $strategy->doesFixtureMatchRequest($fixture, $request);
            $isMatched = $isMatched && $result->isMatched;
            $log .= $result->log . PHP_EOL;
        }

        return new MatchResult($isMatched, $this, trim($log));
    }
}