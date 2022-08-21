<?php

namespace Cspray\HttpClientTestInterceptor\Exception;

use Amp\Http\Client\Request;

class RequestNotMocked extends Exception {

    public static function fromNoMockedRequests() : self {
        return new self('No requests have been mocked. Please call MockingInterceptor::getHttpMocker to add a mocked request and response.');
    }

    public static function fromRequestNotMatched(Request $request) : self {
        return new self(sprintf('No mocks were found to match request %s %s.', $request->getMethod(), $request->getUri()));
    }

}