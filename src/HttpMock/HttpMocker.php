<?php

namespace Cspray\HttpClientTestInterceptor\HttpMock;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;

interface HttpMocker {

    public function onRequest(Request $request, MatcherStrategy $strategy = null) : self;

    public function returnResponse(Response $response) : self;

    public function getFixture() : Fixture;

    public function getMatcherStrategy() : MatcherStrategy;

}