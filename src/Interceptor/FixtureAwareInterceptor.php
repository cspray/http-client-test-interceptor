<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Interceptor;

use Amp\Cancellation;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Fixture\FixtureRepository;
use Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\System\Clock;
use Cspray\HttpClientTestInterceptor\System\SystemClock;

final class FixtureAwareInterceptor implements TestingInterceptor {

    private readonly Clock $clock;

    /** @var list<TestingInterceptorLogger> */
    private array $loggers = [];

    public function __construct(
        private readonly FixtureRepository $fixtureRepository,
        private readonly MatcherStrategy   $requestMatchingStrategy,
        Clock                              $clock = null
    ) {
        $this->clock = $clock ?? new SystemClock();
    }

    public function request(Request $request, Cancellation $cancellation, DelegateHttpClient $httpClient) : Response {
        foreach ($this->fixtureRepository->getFixtures() as $fixture) {
            assert($fixture instanceof Fixture);
            $results = $this->requestMatchingStrategy->doesFixtureMatchRequest($fixture, $request);
            foreach ($this->loggers as $logger) {
                $logger->log($fixture, $request, $results);
            }
            if ($results->isMatched) {
                // The $request might match against what we have stored but there might be attributes or other state-specific
                // stuff that should be included if for some reason the code under test calls $response->getRequest()
                $response = $fixture->getResponse();
                $response->setRequest($request);
                $response->setHeader('HttpClient-TestInterceptor-Fixture-Id', $fixture->getId()->toString());
                return $response;
            }
        }

        $response = $httpClient->request($request, $cancellation);
        $this->fixtureRepository->saveFixture(new InFlightFixture($request, $response, $this->clock->now()));

        return $response;
    }

    public function getFixtureRepository() : FixtureRepository {
        return $this->fixtureRepository;
    }

    public function getRequestMatchingStrategy() : MatcherStrategy {
        return $this->requestMatchingStrategy;
    }

    public function addLogger(TestingInterceptorLogger $logger) : void {
        $this->loggers[] = $logger;
    }

    public function removeLogger(TestingInterceptorLogger $logger) : void {
        foreach ($this->loggers as $index => $storedLogger) {
            if ($logger === $storedLogger) {
                unset($this->loggers[$index]);
            }
        }
    }

    public function getLoggers() : array {
        return $this->loggers;
    }
}