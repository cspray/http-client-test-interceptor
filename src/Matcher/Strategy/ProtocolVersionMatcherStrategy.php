<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
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

        if ($isMatched) {
            $log = 'Fixture and Request protocol versions match';
        } else {
            $diff = $this->differ->diff($fixture->getRequest()->getProtocolVersions(), $request->getProtocolVersions());
            $log = "Fixture and Request protocol versions do not match!\n\n$diff";
        }

        return new MatcherStrategyResult($isMatched, $this, $log);
    }
}