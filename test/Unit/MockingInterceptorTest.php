<?php

namespace Cspray\HttpClientTestInterceptor\Unit;

use Amp\Cancellation;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Exception\InvalidMock;
use Cspray\HttpClientTestInterceptor\Exception\RequestNotMocked;
use Cspray\HttpClientTestInterceptor\HttpMock\MockResponse;
use Cspray\HttpClientTestInterceptor\Interceptor\MockingInterceptor;
use Cspray\HttpClientTestInterceptor\Interceptor\TestingInterceptorLogger;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use League\Uri\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Interceptor\MockingInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\RequestNotMocked
 * @covers \Cspray\HttpClientTestInterceptor\Exception\InvalidMock
 * @covers \Cspray\HttpClientTestInterceptor\HttpMock\MockResponse
 * @covers \Cspray\HttpClientTestInterceptor\System\SystemClock
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\MethodMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\UriMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\ProtocolVersionMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\StrictHeadersMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class MockingInterceptorTest extends TestCase {

    private MockingInterceptor $subject;
    private Cancellation&MockObject $cancellation;
    private DelegateHttpClient&MockObject $client;

    protected function setUp() : void {
        parent::setUp();
        $this->cancellation = $this->getMockBuilder(Cancellation::class)->getMock();
        $this->client = $this->getMockBuilder(DelegateHttpClient::class)->getMock();
        $this->subject = new MockingInterceptor();

        $this->client->expects($this->never())->method('request');
    }

    private function sendRequest(Request $request) : Response {
        return $this->subject->request($request, $this->cancellation, $this->client);
    }

    public function testMockingInterceptorNoMocksDefinedThrowsExceptionOnRequest() : void {
        $expected = <<<TEXT
No matching mocks were found for the given request:

GET /

No fixtures were present to match against!

TEXT;

        $this->expectException(RequestNotMocked::class);
        $this->expectExceptionMessage($expected);

        $this->sendRequest(new Request(Http::createFromString('http://example.com')));
    }

    public function testMockRequestNotMatchedThrowsExceptionOnRequest() : void {
        $httpMock = $this->subject->httpMock();
            $httpMock->onRequest(new Request(Http::createFromString('https://example.com')))
            ->returnResponse(MockResponse::fromBody('my body'));

        $expected = <<<TEXT
No matching mocks were found for the given request:

GET /

Attempted to match against 1 fixture.

Fixture ID {$httpMock->getFixture()->getId()}
************************************ DIFFS ************************************
uri
--- Fixture
+++ Request
@@ @@
-https://example.com
+https://not.example.com

method

headers

body

protocol

TEXT;

        $this->expectException(RequestNotMocked::class);
        $this->expectExceptionMessage($expected);

        $this->sendRequest(new Request(Http::createFromString('https://not.example.com')));
    }

    public function testMockRequestNotMatchedHasMatchResultInException() : void {
        $result = null;
        try {
            $this->subject->httpMock()
                ->onRequest(new Request(Http::createFromString('https://example.com')))
                ->returnResponse(MockResponse::fromBody('my body'));

            $this->sendRequest(new Request(Http::createFromString('https://not.example.com')));
        } catch (RequestNotMocked $requestNotMocked) {
            $result = $requestNotMocked->getMatchResults();
        }

        self::assertNotEmpty($result);
        self::assertContainsOnlyInstancesOf(MatcherStrategyResult::class, $result);
    }

    public function testMockDoesNotProvideRequestAndResponseThrowsException() : void {
        $this->subject->httpMock();

        $this->expectException(InvalidMock::class);
        $this->expectExceptionMessage('An HttpMocker MUST provide a Request to match against AND a Response to return but nothing was provided.');

        $this->sendRequest(new Request(Http::createFromString('https://example.com')));
    }

    public function testDoesNotProvideResponseThrowsException() : void {
        $this->subject->httpMock()->onRequest(new Request(Http::createFromString('http://example.com')));

        $this->expectException(InvalidMock::class);
        $this->expectExceptionMessage('An HttpMocker MUST provide a Response to return but none was provided.');

        $this->sendRequest(new Request(Http::createFromString('https://example.com')));
    }

    public function testDoesNotProvideRequestThrowsException() : void {
        $this->subject->httpMock()->returnResponse(MockResponse::fromBody('http testing'));

        $this->expectException(InvalidMock::class);
        $this->expectExceptionMessage('An HttpMocker MUST provide a Request to match against but none was provided.');

        $this->sendRequest(new Request(Http::createFromString('https://example.com')));
    }

    public function testRequestMatchesReturnsResponse() : void {
        $this->subject->httpMock()
            ->onRequest(new Request(Http::createFromString('https://example.com')))
            ->returnResponse($response = MockResponse::fromBody('my response'));

        $actual = $this->sendRequest(new Request(Http::createFromString('https://example.com')));
        self::assertSame($response, $actual);
    }

    public function testMatchedResponseHasCorrectRequest() : void {
        $this->subject->httpMock()
            ->onRequest(new Request(Http::createFromString('http://something-else.example.com')))
            ->returnResponse(MockResponse::fromBody('something'));

        $actual = $this->sendRequest($request = new Request(Http::createFromString('http://something-else.example.com')));

        self::assertSame($request, $actual->getRequest());
    }

    public function testMatcherResultPassedToLogger() : void {
        $mock = $this->subject->httpMock()->onRequest(new Request('http://example.com'))
            ->returnResponse(MockResponse::fromBody('something'));

        $request = new Request('http://example.com');

        $logger = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with($mock->getFixture(), $request, $this->isInstanceOf(MatcherStrategyResult::class));

        $this->subject->addLogger($logger);

        $this->sendRequest($request);
    }

    public function testGettingAddedObservers() : void {
        $loggerA = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();
        $loggerB = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();

        $this->subject->addLogger($loggerA);
        $this->subject->addLogger($loggerB);

        self::assertSame([$loggerA, $loggerB], $this->subject->getLoggers());
    }

    public function testRemovingObservers() : void {
        $loggerA = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();
        $loggerB = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();

        $this->subject->addLogger($loggerA);
        $this->subject->addLogger($loggerB);

        self::assertSame([$loggerA, $loggerB], $this->subject->getLoggers());

        $this->subject->removeLogger($loggerB);

        self::assertSame([$loggerA], $this->subject->getLoggers());
    }

}