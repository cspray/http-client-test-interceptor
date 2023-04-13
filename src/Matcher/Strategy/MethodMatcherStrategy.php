<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use SebastianBergmann\Diff\Differ;

final class MethodMatcherStrategy implements MatcherStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $isMatched = $fixture->getRequest()->getMethod() === $request->getMethod();

        $diff = '';
        if (!$isMatched) {
            $diff = $this->differ->diff($fixture->getRequest()->getMethod(), $request->getMethod());
        }

        return new MatcherStrategyResult($isMatched, $request, $fixture, $this, [new MatcherDiff('method', $diff)]);
    }
}