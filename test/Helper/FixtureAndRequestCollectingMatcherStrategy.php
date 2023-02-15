<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Helper;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;

final class FixtureAndRequestCollectingMatcherStrategy implements MatcherStrategy {

    private readonly MatcherStrategyResult $result;

    /**
     * @var list<array{0: Fixture, 1: Request}>
     */
    private array $pairs = [];

    public function __construct(
        bool $isMatched,
        string $log
    ) {
        $this->result = new MatcherStrategyResult($isMatched, $this, $log);
    }

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
        $this->pairs[] = [$fixture, $request];
        return $this->result;
    }

    /**
     * @return list<array{0: Fixture, 1: Request}>
     */
    public function getPairs() : array {
        return $this->pairs;
    }
}