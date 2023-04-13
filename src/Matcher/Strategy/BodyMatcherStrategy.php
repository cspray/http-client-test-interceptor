<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use SebastianBergmann\Diff\Differ;

final class BodyMatcherStrategy implements MatcherStrategy {

    public function __construct(private readonly Differ $differ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $fixtureBody = $fixture->getRequest()->getBody()->createBodyStream()->read();
        $requestBody = $request->getBody()->createBodyStream()->read();
        $isMatched = $fixtureBody === $requestBody;

        $diff = '';
        if (!$isMatched) {
            $diff = $this->differ->diff($fixtureBody, $requestBody);
        }

        return new MatcherStrategyResult(
            $isMatched,
            $request,
            $fixture,
            $this,
            [new MatcherDiff('body', $diff)]
        );
    }
}