<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

interface MatcherStrategy {

    /**
     * @param Fixture $fixture
     * @param Request $request
     * @return MatcherStrategyResult
     */
    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult;

}