<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use League\Uri\Components\Query;

final class UriMatcher implements RequestMatchingStrategy {

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool {
        $fixtureUri = $fixture->getRequest()->getUri();
        $requestUri = $request->getUri();

        $fixtureQueryPairs = iterator_to_array(Query::createFromUri($fixtureUri)->pairs());
        $requestQueryPairs = iterator_to_array(Query::createFromUri($requestUri)->pairs());

        ksort($fixtureQueryPairs);
        ksort($requestQueryPairs);

        return $fixtureUri->getScheme() === $requestUri->getScheme() &&
            $fixtureUri->getHost() === $requestUri->getHost() &&
            $fixtureUri->getPort() === $requestUri->getPort() &&
            $fixtureUri->getPath() === $requestUri->getPath() &&
            $fixtureQueryPairs === $requestQueryPairs &&
            $fixtureUri->getFragment() === $requestUri->getFragment();
    }
}