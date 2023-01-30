<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\BodyMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\HeadersMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\MethodMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\ProtocolVersionsMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\RequestMatchingStrategy;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\UriMatcher;

enum Matcher {
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