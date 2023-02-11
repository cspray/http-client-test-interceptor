<?php

namespace Cspray\HttpClientTestInterceptor\Interceptor;

use Amp\Http\Client\ApplicationInterceptor;

interface TestingInterceptor extends ApplicationInterceptor {

    public function addLogger(TestingInterceptorLogger $logger) : void;

    public function removeLogger(TestingInterceptorLogger $logger) : void;

    /**
     * @return list<TestingInterceptorLogger>
     */
    public function getLoggers() : array;

}