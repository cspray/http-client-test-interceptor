<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use SebastianBergmann\Diff\Differ;

final class MethodMatcherStrategy implements MatcherStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $isMatched = $fixture->getRequest()->getMethod() === $request->getMethod();

        if ($isMatched) {
            $log = 'Fixture and Request method match';
        } else {
            $diff = $this->differ->diff($fixture->getRequest()->getMethod(), $request->getMethod());
            $log = "Fixture and Request method do not match!\n\n$diff";
        }

        return new MatcherStrategyResult($isMatched, $this, $log);
    }
}