<?php

namespace Cspray\HttpClientTestInterceptor;

trait HttpMockingTestTrait {

    private ?MockingInterceptor $mockingInterceptor = null;

    public function getMockingInterceptor() : MockingInterceptor {
        if ($this->mockingInterceptor === null) {
            $this->mockingInterceptor = new MockingInterceptor();
        }

        return $this->mockingInterceptor;
    }

    public function httpMock() : HttpMocker {
        return $this->getMockingInterceptor()->httpMock();
    }

}