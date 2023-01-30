<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

interface RequestMatchingStrategy {

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool;

}