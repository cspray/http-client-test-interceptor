<?php

namespace Cspray\HttpClientTestInterceptor\Helper;

use Cspray\HttpClientTestInterceptor\System\Clock;
use DateTimeImmutable;

final class FixedClock implements Clock {

    public function __construct(
        private readonly DateTimeImmutable $now
    ) {}

    public function now() : DateTimeImmutable {
        return $this->now;
    }

}