<?php

namespace Cspray\HttpClientTestInterceptor\Exception;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\MatchResult;

class RequestNotMocked extends Exception {

    private readonly array $matchResults;

    public function __construct(string $message, array $matchResults = []) {
        parent::__construct($message);
        $this->matchResults = $matchResults;
    }

    public static function fromNoMockedRequests() : self {
        return new self('No requests have been mocked. Please call MockingInterceptor::getHttpMocker to add a mocked request and response.');
    }

    public static function fromRequestNotMatched(Request $request, array $matchResults) : self {
        return new self(sprintf('No mocks were found to match request %s %s.', $request->getMethod(), $request->getUri()), $matchResults);
    }

    public function getMatchResults() : array {
        return $this->matchResults;
    }

}