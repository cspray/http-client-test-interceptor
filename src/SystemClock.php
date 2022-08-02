<?php

namespace Cspray\HttpClientTestInterceptor;

use DateTimeImmutable;

final class SystemClock implements Clock {

    public function now() : DateTimeImmutable {
        return new DateTimeImmutable();
    }
}