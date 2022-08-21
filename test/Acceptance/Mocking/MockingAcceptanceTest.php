<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\Mocking;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\HttpMockingTestTrait;
use Cspray\HttpClientTestInterceptor\MockResponse;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\MockingInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\HttpMockingTestTrait::getMockingInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\HttpMockingTestTrait::httpMock
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture
 * @covers \Cspray\HttpClientTestInterceptor\MockResponse
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\MethodMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\UriMatcher
 * @covers \Cspray\HttpClientTestInterceptor\SystemClock
 */
class MockingAcceptanceTest extends TestCase {

    use HttpMockingTestTrait;

    public function testGetInterceptorSameInstance() : void {
        self::assertSame(
            $this->getMockingInterceptor(),
            $this->getMockingInterceptor()
        );
    }

    public function testMockSuccessfulResponse() : void {
        $this->httpMock()->whenClientReceivesRequest(new Request(Http::createFromString('http://example.com')))
            ->willReturnResponse($response = MockResponse::fromBody('body'));

        $client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();

        $actual = $client->request(new Request(Http::createFromString('http://example.com')));

        self::assertSame($response, $actual);
    }

}