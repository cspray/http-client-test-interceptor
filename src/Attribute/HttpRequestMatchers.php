<?php

namespace Cspray\HttpClientTestInterceptor\Attribute;

use Attribute;
use Cspray\HttpClientTestInterceptor\Matcher;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class HttpRequestMatchers {

    public readonly array $matchers;

    public function __construct(
        Matcher $matchers,
        Matcher... $additionalMatchers
    ) {
        $this->matchers = [$matchers, ...$additionalMatchers];
    }

}
