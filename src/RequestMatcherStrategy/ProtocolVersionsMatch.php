<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatchResult;
use SebastianBergmann\Diff\Differ;

final class ProtocolVersionsMatch implements RequestMatchStrategy {

    public function __construct(
        private readonly Differ $differ
    ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatchResult {
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

        return new MatchResult($isMatched, $this, $log);
    }
}