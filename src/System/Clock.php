<?php

namespace Cspray\HttpClientTestInterceptor\System;

use DateTimeImmutable;

interface Clock {

    public function now() : DateTimeImmutable;

}