<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\RequestMatchingStrategy;

enum Matchers {
    case Body;
    case Headers;
    case Method;
    case ProtocolVersions;
    case Uri;

    public function getStrategy() : RequestMatchingStrategy {
        static $cache = [];
        if (!isset($cache[$this->name])) {
            $cache[$this->name] = match($this) {
                Matchers::Body => new BodyMatcher(),
                Matchers::Headers => new HeadersMatcher(),
                Matchers::Method => new MethodMatcher(),
                Matchers::ProtocolVersions => new ProtocolVersionsMatcher(),
                Matchers::Uri => new UriMatcher()
            };
        }

        return $cache[$this->name];
    }
}