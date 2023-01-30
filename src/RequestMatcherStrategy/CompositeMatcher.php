<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher;

final class CompositeMatcher implements RequestMatchingStrategy {

    /**
     * @var RequestMatchingStrategy[]
     */
    private readonly array $strategies;

    public function __construct(
        RequestMatchingStrategy $strategy,
        RequestMatchingStrategy... $additionalStrategies
    ) {
        $this->strategies = array_merge([$strategy], $additionalStrategies);
    }

    public static function fromMatchers(Matcher $matchers, Matcher... $additionalMatchers) : self {
        $strategies = [$matchers->getStrategy()];
        foreach ($additionalMatchers as $matcher) {
            $strategies[] = $matcher->getStrategy();
        }
        return new self(...$strategies);
    }

    public function getStrategies() : array {
        return $this->strategies;
    }

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool {
        foreach ($this->strategies as $strategy) {
            if (!$strategy->doesFixtureMatchRequest($fixture, $request)) {
                return false;
            }
        }
        return true;
    }
}