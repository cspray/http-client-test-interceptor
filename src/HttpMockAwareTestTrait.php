<?php

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\HttpMock\HttpMocker;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerRequiredInvocations;
use Cspray\HttpClientTestInterceptor\Interceptor\MockingInterceptor;

trait HttpMockAwareTestTrait {

    private ?MockingInterceptor $mockingInterceptor = null;

    public function getMockingInterceptor() : MockingInterceptor {
        if ($this->mockingInterceptor === null) {
            $this->mockingInterceptor = new MockingInterceptor();
        }

        return $this->mockingInterceptor;
    }

    public function validateHttpMocks(HttpMockerRequiredInvocations $requiredInvocations = HttpMockerRequiredInvocations::All) : void {
        $this->getMockingInterceptor()->validate($requiredInvocations);
    }

    public function httpMock() : HttpMocker {
        return $this->getMockingInterceptor()->httpMock();
    }

}