<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use League\Uri\Components\Query;
use SebastianBergmann\Diff\Differ;

final class UriMatcherStrategy implements MatcherStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
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
            /*
            $fixtureQueryPairs === $requestQueryPairs &&*/
            $fixtureUri->getFragment() === $requestUri->getFragment();

        $diff = '';
        if (!$isMatched) {
            $diff = $this->differ->diff((string) $fixture->getRequest()->getUri(), (string) $request->getUri());
        }

        return new MatcherStrategyResult($isMatched, $request, $fixture, $this, [new MatcherDiff('uri', $diff)]);
    }
}