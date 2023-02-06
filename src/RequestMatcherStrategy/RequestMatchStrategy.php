<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\MatchResult;

interface RequestMatchStrategy {

    /**
     * @param Fixture $fixture
     * @param Request $request
     * @return MatchResult
     */
    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatchResult;

}