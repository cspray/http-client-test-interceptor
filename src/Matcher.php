<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\BodyMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\StrictHeadersMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\ProtocolVersionsMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\RequestMatcherStrategy;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\UriMatcher;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

enum Matcher {
    case All;
    case Body;
    case Headers;
    case Method;
    case ProtocolVersions;
    case Uri;

    public function getStrategy() : RequestMatcherStrategy {
        static $cache = [];
        if (!isset($cache[$this->name])) {
            $cache[$this->name] = match($this) {
                self::Body => new BodyMatcher($this->getDiffer()),
                self::Headers => new StrictHeadersMatcher($this->getDiffer()),
                self::Method => new MethodMatcher($this->getDiffer()),
                self::ProtocolVersions => new ProtocolVersionsMatcher($this->getDiffer()),
                self::Uri => new UriMatcher($this->getDiffer()),
                self::All => $this->createCompositeMatcher()
            };
        }

        return $cache[$this->name];
    }

    private function createCompositeMatcher() : CompositeMatcher {
        return CompositeMatcher::fromMatchers(
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