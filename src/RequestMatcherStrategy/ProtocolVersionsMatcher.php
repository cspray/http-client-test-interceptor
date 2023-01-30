<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

final class ProtocolVersionsMatcher implements RequestMatchingStrategy {

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool {
        $fixtureProtocols = $fixture->getRequest()->getProtocolVersions();
        $requestProtocols = $request->getProtocolVersions();

        sort($fixtureProtocols);
        sort($requestProtocols);

        return $fixtureProtocols === $requestProtocols;
    }
}