<?php

namespace Cspray\HttpClientTestInterceptor;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;

interface HttpMockerValidator {

    public function matches(Request $request) : HttpMockerResult;

    public function hasMockBeenMatched() : bool;

}