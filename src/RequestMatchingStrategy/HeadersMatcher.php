<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

final class HeadersMatcher implements RequestMatchingStrategy {

    public function __construct() {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool {
        $fixtureHeaders = [];
        $requestHeaders = [];

        foreach ($fixture->getRequest()->getHeaders() as $name => $values) {
            sort($values);
            $fixtureHeaders[$name] = $values;
        }

        foreach ($request->getHeaders() as $name => $values) {
            sort($values);
            $requestHeaders[$name] = $values;
        }

        ksort($fixtureHeaders);
        ksort($requestHeaders);

        return $fixtureHeaders === $requestHeaders;
    }
}