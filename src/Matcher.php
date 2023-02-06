<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\BodyMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\CompositeMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\StrictHeadersMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\ProtocolVersionsMatch;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatchStrategy;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatch;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

enum Matcher {
    case All;
    case Body;
    case Headers;
    case Method;
    case ProtocolVersions;
    case Uri;

    public function getStrategy() : RequestMatchStrategy {
        static $cache = [];
        if (!isset($cache[$this->name])) {
            $cache[$this->name] = match($this) {
                self::Body => new BodyMatch($this->getDiffer()),
                self::Headers => new StrictHeadersMatch($this->getDiffer()),
                self::Method => new MethodMatch($this->getDiffer()),
                self::ProtocolVersions => new ProtocolVersionsMatch($this->getDiffer()),
                self::Uri => new UriMatch($this->getDiffer()),
                self::All => $this->createCompositeMatcher()
            };
        }

        return $cache[$this->name];
    }

    private function createCompositeMatcher() : CompositeMatch {
        return CompositeMatch::fromMatchers(
            self::Uri,
            self::Method,
            self::Headers,
            self::Body,
            self::ProtocolVersions,
        );
    }

    private function getDiffer() : Differ {
        static $differ = null;
        if ($differ === null) {
            $differ = new Differ(
                new UnifiedDiffOutputBuilder("--- Fixture\n+++ Request\n", false)
            );
        }

        return $differ;
    }
}