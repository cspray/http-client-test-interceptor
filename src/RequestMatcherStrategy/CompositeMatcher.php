<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatcherResult;

final class CompositeMatcher implements RequestMatcherStrategy {

    /**
     * @var Matcher[]
     */
    private readonly array $matchers;

    private function __construct(
        Matcher $matcher,
        Matcher... $additionalMatchers
    ) {
        $this->matchers = array_merge([$matcher], $additionalMatchers);
    }

    public static function fromMatchers(Matcher $matcher, Matcher... $additionalMatchers) : self {
        return new self($matcher, ...$additionalMatchers);
    }

    public function getMatchers() : array {
        return $this->matchers;
    }

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : array {
        $results = [];
        foreach ($this->matchers as $matcher) {
            $matcherResults = $matcher->getStrategy()->doesFixtureMatchRequest($fixture, $request);
            foreach ($matcherResults as $result) {
                $results[] = $result;
            }
        }
        return $results;
    }
}