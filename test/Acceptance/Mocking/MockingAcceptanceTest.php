<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\Mocking;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Exception\RequiredMockRequestsNotSent;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerRequiredInvocations;
use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockingTestTrait;
use Cspray\HttpClientTestInterceptor\HttpMock\MockResponse;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Interceptor\MockingInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\HttpMockingTestTrait::getMockingInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\HttpMockingTestTrait::httpMock
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\HttpMockingTestTrait::validateHttpMocks
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\RequiredMockRequestsNotSent
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\MockResponse
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\MethodMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\UriMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\SystemClock
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerRequiredInvocations
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerResult
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

    public function testMockRequestClientNeverCalledThrowsException() : void {
        $this->httpMock()->whenClientReceivesRequest(new Request('http://example.com'))
            ->willReturnResponse(MockResponse::fromBody('my body'));

        (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();

        $this->expectException(RequiredMockRequestsNotSent::class);
        $this->expectExceptionMessage(
            'There are 1 mocked HTTP interactions but 0 had a matching Request. All mocked HTTP interactions must be requested.'
        );

        $this->validateHttpMocks();
    }

    public function testMockRequestClientMultipleRequestsSomeMatched() : void {
        $this->httpMock()->whenClientReceivesRequest(new Request('http://one.example.com'))
            ->willReturnResponse(MockResponse::fromBody('first response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://two.example.com'))
            ->willReturnResponse(MockResponse::fromBody('second response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://three.example.com'))
            ->willReturnResponse(MockResponse::fromBody('third response'));

        $client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();

        $client->request(new Request('http://one.example.com'));
        $client->request(new Request('http://three.example.com'));

        $this->expectException(RequiredMockRequestsNotSent::class);
        $this->expectExceptionMessage('There are 3 mocked HTTP interactions but 2 had a matching Request. All mocked HTTP interactions must be requested.');

        $this->validateHttpMocks();
    }

    public function testMockRequestClientMultipleRequestsAnyCheck() : void {
        $this->httpMock()->whenClientReceivesRequest(new Request('http://one.example.com'))
            ->willReturnResponse(MockResponse::fromBody('first response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://two.example.com'))
            ->willReturnResponse(MockResponse::fromBody('second response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://three.example.com'))
            ->willReturnResponse(MockResponse::fromBody('third response'));

        $client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();

        $this->expectException(RequiredMockRequestsNotSent::class);
        $this->expectExceptionMessage(
            'There are 3 mocked HTTP interactions but 0 had a matching Request. At least 1 mocked HTTP interaction must be requested.'
        );

        $this->validateHttpMocks(HttpMockerRequiredInvocations::Any);
    }

    public function testMockRequestClientMultipleRequestsAnyCheckHasAtLeastOne() : void {
        $this->expectNotToPerformAssertions();

        $this->httpMock()->whenClientReceivesRequest(new Request('http://one.example.com'))
            ->willReturnResponse(MockResponse::fromBody('first response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://two.example.com'))
            ->willReturnResponse(MockResponse::fromBody('second response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://three.example.com'))
            ->willReturnResponse(MockResponse::fromBody('third response'));

        $client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();

        $client->request(new Request('http://one.example.com'));

        $this->validateHttpMocks(HttpMockerRequiredInvocations::Any);
    }

    public function testMockRequestClientMultipleRequestsNone() : void {
        $this->expectNotToPerformAssertions();

        $this->httpMock()->whenClientReceivesRequest(new Request('http://one.example.com'))
            ->willReturnResponse(MockResponse::fromBody('first response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://two.example.com'))
            ->willReturnResponse(MockResponse::fromBody('second response'));

        $this->httpMock()->whenClientReceivesRequest(new Request('http://three.example.com'))
            ->willReturnResponse(MockResponse::fromBody('third response'));

        (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();

        $this->validateHttpMocks(HttpMockerRequiredInvocations::None);
    }

}