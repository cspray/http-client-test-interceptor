<?php

namespace Cspray\HttpClientTestInterceptor\Exception;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;

class RequestNotMocked extends Exception {

    private readonly array $matchResults;

    public function __construct(string $message, array $matchResults = []) {
        parent::__construct($message);
        $this->matchResults = $matchResults;
    }

    /**
     * @param list<MatcherStrategyResult> $results
     * @return self
     */
    public static function fromMatcherStrategyResults(Request $request, array $results) : self {
        $numResults = count($results);
        $fixtureOrFixtures = $numResults === 1 ? 'fixture' : 'fixtures';
        $bannerLine = str_repeat('*', 36) . ' DIFFS ' . str_repeat('*', 36);
        $path = $request->getUri()->getPath();

        if ($numResults === 0) {
            $matchingDescription = 'No fixtures were present to match against!';
        } else {
            $matchingDescription = 'Attempted to match against ' . $numResults . ' ' . $fixtureOrFixtures . '.';
        }

        if ($path === '') {
            $path = '/';
        }
        $message = <<<TEXT
No matching mocks were found for the given request:

{$request->getMethod()} {$path}

{$matchingDescription}

TEXT;
        foreach ($results as $result) {
            $message .= <<<TEXT

Fixture ID {$result->fixture->getId()}
{$bannerLine}

TEXT;
            foreach ($result->diffs as $diff) {
                $message .= <<<TEXT
{$diff->label}
{$diff->diff}

TEXT;

            }
        }

        return new self($message, $results);
    }

    public function getMatchResults() : array {
        return $this->matchResults;
    }

}