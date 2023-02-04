<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatcherResult;
use SebastianBergmann\Diff\Differ;

final class MethodMatcher implements RequestMatcherStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : array {
        $isMatched = $fixture->getRequest()->getMethod() === $request->getMethod();

        if ($isMatched) {
            $log = 'Fixture and Request method match';
        } else {
            $diff = $this->differ->diff($fixture->getRequest()->getMethod(), $request->getMethod());
            $log = "Fixture and Request method do not match!\n\n$diff";
        }

        return [
            new MatcherResult($isMatched, $this, $log)
        ];
    }
}