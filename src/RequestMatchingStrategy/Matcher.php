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
                self::Body => new BodyMatcher(),
                self::Headers => new HeadersMatcher(),
                self::Method => new MethodMatcher(),
                self::ProtocolVersions => new ProtocolVersionsMatcher(),
                self::Uri => new UriMatcher()
            };
        }

        return $cache[$this->name];
    }
}