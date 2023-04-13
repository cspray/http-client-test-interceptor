<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Matcher;

final class MatcherDiff {

    public function __construct(
        public readonly string $label,
        public readonly string $diff
    ) {}

}
