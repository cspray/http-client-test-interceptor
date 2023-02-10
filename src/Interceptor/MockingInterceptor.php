<?php

namespace Cspray\HttpClientTestInterceptor\Interceptor;

use Amp\Cancellation;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Clock;
use Cspray\HttpClientTestInterceptor\Exception\InvalidMock;
use Cspray\HttpClientTestInterceptor\Exception\RequestNotMocked;
use Cspray\HttpClientTestInterceptor\Exception\RequiredMockRequestsNotSent;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMocker;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerRequiredInvocations;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\SystemClock;

class MockingInterceptor implements TestingInterceptor {

    /** @var list<array{'mock': HttpMocker, 'matchCount': int}> */
    private array $httpMockers = [];

    private readonly Clock $clock;

    public function __construct(
        Clock $clock = null
    ) {
        $this->clock = $clock ?? new SystemClock();
    }

    public function httpMock() : HttpMocker {
        $mocker = new class($this->clock) implements HttpMocker {
            private ?Request $request = null;
            private ?Response $response = null;
            private ?MatcherStrategy $matchingStrategy = null;

            public function __construct(
                private readonly Clock $clock
            ) {}

            public function onRequest(Request $request, MatcherStrategy $strategy = null) : HttpMocker {
                $this->matchingStrategy = $strategy ?? Matcher::All->getStrategy();
                $this->request = $request;
                return $this;
            }

            public function returnResponse(Response $response) : HttpMocker {
                $this->response = $response;
                return $this;
            }

            public function getFixture() : Fixture {
                if ($this->request === null && $this->response === null) {
                    throw InvalidMock::fromNoRequestAndResponse();
                }

                if ($this->response === null) {
                    throw InvalidMock::fromNoResponse();
                }

                if ($this->request === null) {
                    throw InvalidMock::fromNoRequest();
                }

                return new InFlightFixture($this->request, $this->response, $this->clock->now());
            }

            public function getMatcherStrategy() : MatcherStrategy {
                return $this->matchingStrategy;
            }
        };

        $this->httpMockers[] = [
            'mock' => $mocker,
            'matchCount' => 0
        ];

        return $mocker;
    }

    public function validate(HttpMockerRequiredInvocations $requiredInvocations = HttpMockerRequiredInvocations::All) : void {
        $totalMocks = count($this->httpMockers);
        $matchedMocks = 0;

        foreach ($this->httpMockers as $mocker) {
            if ($mocker['matchCount'] > 0) {
                $matchedMocks++;
            }
        }

        if ($totalMocks !== $matchedMocks && $requiredInvocations->isAll()) {
            throw RequiredMockRequestsNotSent::fromMissingRequiredInvocations($totalMocks, $matchedMocks, $requiredInvocations);
        }

        if ($matchedMocks === 0 && $requiredInvocations->isAny()) {
            throw RequiredMockRequestsNotSent::fromMissingRequiredInvocations($totalMocks, 0, $requiredInvocations);
        }
    }

    public function request(Request $request, Cancellation $cancellation, DelegateHttpClient $httpClient) : Response {
        if ($this->httpMockers === []) {
            throw RequestNotMocked::fromNoMockedRequests();
        }

        $results = [];
        foreach ($this->httpMockers as $index => $httpMocker) {
            $fixture = $httpMocker['mock']->getFixture();
            $matcherStrategy = $httpMocker['mock']->getMatcherStrategy();

            $matcherResults = $matcherStrategy->doesFixtureMatchRequest($fixture, $request);
            $results[] = $matcherResults;
            if ($matcherResults->isMatched) {
                $this->httpMockers[$index]['matchCount']++;
                $response = $fixture->getResponse();
                $response->setRequest($request);
                return $response;
            }
        }

        throw RequestNotMocked::fromRequestNotMatched($request, $results);
    }
}