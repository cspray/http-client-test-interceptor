<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use SebastianBergmann\Diff\Differ;

final class ProtocolVersionMatcherStrategy implements MatcherStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $fixtureProtocols = $fixture->getRequest()->getProtocolVersions();
        $requestProtocols = $request->getProtocolVersions();

        sort($fixtureProtocols);
        sort($requestProtocols);

        $isMatched = $fixtureProtocols === $requestProtocols;

        $diff = '';
        if (!$isMatched) {
            $diff = $this->differ->diff(
                implode(', ', $fixtureProtocols),
                implode(', ', $requestProtocols)
            );
        }

        return new MatcherStrategyResult($isMatched, $request, $fixture, $this, [new MatcherDiff('protocol', $diff)]);
    }
}
