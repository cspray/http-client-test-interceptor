<?php

namespace Cspray\HttpClientTestInterceptor\HttpMock;

use Amp\Http\Client\Request;

interface HttpMockerValidator {

    public function matches(Request $request) : HttpMockerResult;

    public function hasMockBeenMatched() : bool;

}