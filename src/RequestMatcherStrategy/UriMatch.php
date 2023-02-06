<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\MatchResult;
use League\Uri\Components\Query;
use SebastianBergmann\Diff\Differ;

final class UriMatch implements RequestMatchStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatchResult {
        $fixtureUri = $fixture->getRequest()->getUri();
        $requestUri = $request->getUri();

        $fixtureQueryPairs = iterator_to_array(Query::createFromUri($fixtureUri)->pairs());
        $requestQueryPairs = iterator_to_array(Query::createFromUri($requestUri)->pairs());

        ksort($fixtureQueryPairs);
        ksort($requestQueryPairs);

        $isMatched = $fixtureUri->getScheme() === $requestUri->getScheme() &&
            $fixtureUri->getHost() === $requestUri->getHost() &&
            $fixtureUri->getPort() === $requestUri->getPort() &&
            $fixtureUri->getPath() === $requestUri->getPath() &&
            $fixtureQueryPairs === $requestQueryPairs &&
            $fixtureUri->getFragment() === $requestUri->getFragment();

        if ($isMatched) {
            $log = 'Fixture and Request URI match';
        } else {
            $diff = $this->differ->diff((string) $fixture->getRequest()->getUri(), (string) $request->getUri());
            $log = "Fixture and Request URI do not match!\n\n$diff";
        }

        return new MatchResult($isMatched, $this, $log);
    }
}