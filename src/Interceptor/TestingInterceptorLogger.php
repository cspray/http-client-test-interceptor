<?php

namespace Cspray\HttpClientTestInterceptor\Interceptor;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;

interface TestingInterceptorLogger {

    /**
     * @param Fixture $fixture
     * @param Request $request
     * @param MatcherStrategyResult $result
     * @return void
     */
    public function log(Fixture $fixture, Request $request, MatcherStrategyResult $result) : void;

}