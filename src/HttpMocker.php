<?php

namespace Cspray\HttpClientTestInterceptor;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matcher;

interface HttpMocker {

    public function whenClientReceivesRequest(Request $request, array $matchers = [Matcher::Method, Matcher::Uri]) : self;

    public function willReturnResponse(Response $response) : self;

}