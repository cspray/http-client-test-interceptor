<?php

namespace Cspray\HttpClientTestInterceptor\HttpMock;

enum HttpMockerRequiredInvocations {
    case None;
    case All;
    case Any;

    public function isAll() : bool {
        return $this === self::All;
    }

    public function isAny() : bool {
        return $this === self::Any;
    }
}