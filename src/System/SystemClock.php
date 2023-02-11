<?php

namespace Cspray\HttpClientTestInterceptor\System;

use DateTimeImmutable;

final class SystemClock implements Clock {

    public function now() : DateTimeImmutable {
        return new DateTimeImmutable();
    }
}