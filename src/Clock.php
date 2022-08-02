<?php

namespace Cspray\HttpClientTestInterceptor;

use DateTimeImmutable;

interface Clock {

    public function now() : DateTimeImmutable;

}