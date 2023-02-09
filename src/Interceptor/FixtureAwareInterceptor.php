<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Interceptor;

use Amp\Cancellation;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Clock;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Fixture\FixtureRepository;
use Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\SystemClock;

final class FixtureAwareInterceptor implements ApplicationInterceptor {

    private readonly Clock $clock;

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
}