<?php

namespace Cspray\HttpClientTestInterceptor\RequestMatchingStrategy;

final class MatcherFailureLogs {

    /**
     * @var list<array{matcher: Matcher, msg: string}>
     */
    private array $logs = [];

    public function addLog(Matcher $matcher, string $msg) : void {

    }

    /**
     * @return list<array{matcher: Matcher, msg: string}>
     */
    public function getLogs() : array {

    }

}
