<?php

namespace Cspray\HttpClientTestInterceptor\Interceptor;

use Amp\Cancellation;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Clock;
use Cspray\HttpClientTestInterceptor\Exception\InvalidMock;
use Cspray\HttpClientTestInterceptor\Exception\RequestNotMocked;
use Cspray\HttpClientTestInterceptor\Exception\RequiredMockRequestsNotSent;
use Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMocker;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerRequiredInvocations;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerResult;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerValidator;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy;
use Cspray\HttpClientTestInterceptor\SystemClock;

class MockingInterceptor implements ApplicationInterceptor {

    /** @var list<HttpMockerValidator> */
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
            public ?MatcherStrategy $matchingStrategy = null;

            public function whenClientReceivesRequest(Request $request, array $matchers = [Matcher::Method, Matcher::Uri]) : HttpMocker {
                if ($matchers === []) {
                    throw InvalidMock::fromEmptyMatchers();
                }
                $this->matchingStrategy = CompositeMatcherStrategy::fromMatchers(...$matchers);
                $this->request = $request;
                return $this;
            }

            public function willReturnResponse(Response $response) : HttpMocker {
                $this->response = $response;
                return $this;
            }
        };

        $mockerValidator = new class($mocker, $this->clock) implements HttpMockerValidator {

            private bool $isMatched = false;

            /**
             * @param HttpMocker&object{request: ?Request, response: ?Response, matchingStrategy: ?MatcherStrategy} $mocker
             */
            public function __construct(
                private readonly HttpMocker $mocker,
                private readonly Clock $clock
            ) {}

            public function matches(Request $request) : HttpMockerResult {
                if ($this->mocker->request === null && $this->mocker->response === null) {
                    throw InvalidMock::fromNoRequestAndResponse();
                }

                if ($this->mocker->response === null) {
                    throw InvalidMock::fromNoResponse();
                }

                if ($this->mocker->request === null) {
                    throw InvalidMock::fromNoRequest();
                }

                $fixture = new InFlightFixture($this->mocker->request, $this->mocker->response, $this->clock->now());
                $results = $this->mocker->matchingStrategy->doesFixtureMatchRequest($fixture, $request);
                $response = null;
                if ($results->isMatched) {
                    $response = $fixture->getResponse();
                    $response->setRequest($request);
                    $this->isMatched = true;
                }

                return new HttpMockerResult($response, $results);
            }

            public function hasMockBeenMatched() : bool {
                return $this->isMatched;
            }
        };

        $this->httpMockers[] = $mockerValidator;

        return $mocker;
    }

    public function validate(HttpMockerRequiredInvocations $requiredInvocations = HttpMockerRequiredInvocations::All) : void {
        $totalMocks = count($this->httpMockers);
        $matchedMocks = 0;

        foreach ($this->httpMockers as $mocker) {
            if ($mocker->hasMockBeenMatched()) {
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
        foreach ($this->httpMockers as $httpMocker) {
            $mockResults = $httpMocker->matches($request);
            $results[] = $mockResults->matchResult;
            if ($mockResults->response instanceof Response) {
                return $mockResults->response;
            }
        }

        throw RequestNotMocked::fromRequestNotMatched($request, $results);
    }
}