<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;

final class BodyMatcher implements RequestMatchingStrategy {

    public function __construct() {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : bool {
        $fixtureBody = $fixture->getRequest()->getBody();
        $requestBody = $request->getBody();
        return $fixtureBody->createBodyStream()->read() === $requestBody->createBodyStream()->read();
    }
}