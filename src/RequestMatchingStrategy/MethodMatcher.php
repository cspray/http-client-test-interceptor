<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

final class MethodMatcher implements RequestMatchingStrategy {

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool {
        return $fixture->getRequest()->getMethod() === $request->getMethod();
    }
}