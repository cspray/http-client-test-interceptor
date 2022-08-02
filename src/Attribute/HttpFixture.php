<?php

namespace Cspray\HttpClientTestInterceptor\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class HttpFixture {

    /**
     * @param string $path
     */
    public function __construct(
        public readonly string $path,
    ) {}

}
