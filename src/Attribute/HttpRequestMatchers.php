<?php

namespace Cspray\HttpClientTestInterceptor\Attribute;

use Attribute;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class HttpRequestMatchers {

    public readonly array $matchers;

    public function __construct(
        Matchers $matchers,
        Matchers... $additionalMatchers
    ) {
        $this->matchers = [$matchers, ...$additionalMatchers];
    }

}
