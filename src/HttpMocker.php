<?php

namespace Cspray\HttpClientTestInterceptor;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;

interface HttpMocker {

    public function whenClientReceivesRequest(Request $request, array $matchers = [Matchers::Method, Matchers::Uri]) : self;

    public function willReturnResponse(Response $response) : self;

}