<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher\Strategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use SebastianBergmann\Diff\Differ;

final class StrictHeadersMatcherStrategy implements MatcherStrategy {

    public function __construct(private readonly Differ $differ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatcherStrategyResult {
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

        $isMatched = $fixtureHeaders === $requestHeaders;

        $diff = '';
        if (!$isMatched) {
            $diff = $this->differ->diff(
                $this->formatRawHeaders($fixture->getRequest()->getHeaderPairs()),
                $this->formatRawHeaders($request->getHeaderPairs())
            );
        }

        return new MatcherStrategyResult($isMatched, $request, $fixture, $this, [new MatcherDiff('headers', $diff)]);
    }

    private function formatRawHeaders(array $headers) : string {
        $content = '';
        foreach ($headers as [$field, $value]) {
            $content .= "$field: $value\n";
        }

        return trim($content);
    }
}