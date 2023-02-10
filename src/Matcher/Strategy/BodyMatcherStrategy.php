<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use SebastianBergmann\Diff\Differ;

final class BodyMatcherStrategy implements MatcherStrategy {

    public function __construct(private readonly Differ $differ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $fixtureBody = $fixture->getRequest()->getBody()->createBodyStream()->read();
        $requestBody = $request->getBody()->createBodyStream()->read();
        $isMatched = $fixtureBody === $requestBody;

        if ($isMatched) {
            $log = "Fixture and Request body match";
        } else {
            $diff = $this->differ->diff($fixtureBody, $requestBody);
            $log = "Fixture and Request body do not match!\n\n$diff";
        }

        return new MatcherStrategyResult($isMatched, $this, $log);
    }
}