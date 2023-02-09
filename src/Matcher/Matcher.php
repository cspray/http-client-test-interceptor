<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher;

use Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\MethodMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\ProtocolVersionMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\StrictHeadersMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\UriMatcherStrategy;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

enum Matcher {
    case All;
    case Body;
    case Headers;
    case Method;
    case ProtocolVersions;
    case Uri;

    public function getStrategy() : MatcherStrategy {
        static $cache = [];
        if (!isset($cache[$this->name])) {
            $cache[$this->name] = match($this) {
                self::Body => new BodyMatcherStrategy($this->getDiffer()),
                self::Headers => new StrictHeadersMatcherStrategy($this->getDiffer()),
                self::Method => new MethodMatcherStrategy($this->getDiffer()),
                self::ProtocolVersions => new ProtocolVersionMatcherStrategy($this->getDiffer()),
                self::Uri => new UriMatcherStrategy($this->getDiffer()),
                self::All => $this->createCompositeMatcher()
            };
        }

        return $cache[$this->name];
    }

    private function createCompositeMatcher() : CompositeMatcherStrategy {
        return CompositeMatcherStrategy::fromMatchers(
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