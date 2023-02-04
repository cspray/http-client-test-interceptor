<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\MatcherResult;

interface RequestMatcherStrategy {

    /**
     * @param Fixture $fixture
     * @param Request $request
     * @return list<MatcherResult>
     */
    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : array;

}