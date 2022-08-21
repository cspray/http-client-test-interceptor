<?php

namespace Cspray\HttpClientTestInterceptor;

use Amp\Cancellation;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Exception\InvalidMock;
use Cspray\HttpClientTestInterceptor\Exception\RequestNotMocked;
use Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\RequestMatchingStrategy;

class MockingInterceptor implements ApplicationInterceptor {

    /** @var list<HttpMocker> */
    private array $httpMockers = [];

    private readonly Clock $clock;

    public function __construct(
        Clock $clock = null
    ) {
        $this->clock = $clock ?? new SystemClock();
    }

    public function httpMock() : HttpMocker {
        $mocker = new class implements HttpMocker {
            public ?Request $request = null;
            public ?Response $response = null;
            public ?RequestMatchingStrategy $matchingStrategy = null;

            public function whenClientReceivesRequest(Request $request, array $matchers = [Matchers::Method, Matchers::Uri]) : HttpMocker {
                if ($matchers === []) {
                    throw InvalidMock::fromEmptyMatchers();
                }
                $this->matchingStrategy = CompositeMatcher::fromMatchers(...$matchers);
                $this->request = $request;
                return $this;
            }

            public function willReturnResponse(Response $response) : HttpMocker {
                $this->response = $response;
                return $this;
            }
        };
        $this->httpMockers[] = $mocker;

        return $mocker;
    }

    public function request(Request $request, Cancellation $cancellation, DelegateHttpClient $httpClient) : Response {
        if ($this->httpMockers === []) {
            throw RequestNotMocked::fromNoMockedRequests();
        }

        foreach ($this->httpMockers as $httpMocker) {
            if ($httpMocker->request === null && $httpMocker->response === null) {
                throw InvalidMock::fromNoRequestAndResponse();
            } else if ($httpMocker->response === null) {
                throw InvalidMock::fromNoResponse();
            } else if ($httpMocker->request === null) {
                throw InvalidMock::fromNoRequest();
            }
            $fixture = new InFlightFixture($httpMocker->request, $httpMocker->response, $this->clock->now());
            if ($httpMocker->matchingStrategy->doesFixtureMatchRequest($fixture, $request)) {
                $response = $fixture->getResponse();
                $response->setRequest($request);
                return $response;
            }
        }
        throw RequestNotMocked::fromRequestNotMatched($request);
    }
}