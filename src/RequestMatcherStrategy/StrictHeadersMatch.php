<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\MatchResult;
use SebastianBergmann\Diff\Differ;

final class StrictHeadersMatch implements RequestMatchStrategy {

    public function __construct(private readonly Differ $differ) {}

    public function doesFixtureMatchRequest(Fixture $fixture, Request $request) : MatchResult {
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

        if ($isMatched) {
            $log = 'Fixture and Request headers strictly match';
        } else {
            $log = "Fixture and Request headers do not strictly match!\n\n";
            $log .= $this->differ->diff(
                $this->formatRawHeaders($fixture->getRequest()->getRawHeaders()),
                $this->formatRawHeaders($request->getRawHeaders())
            );
        }

        return new MatchResult($isMatched, $this, $log);
    }

    private function formatRawHeaders(array $headers) : string {
        $content = '';
        foreach ($headers as [$field, $value]) {
            $content .= "$field: $value\n";
        }

        return trim($content);
    }
}