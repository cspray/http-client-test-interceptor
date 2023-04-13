<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit;

use Amp\ByteStream\ReadableBuffer;
use Amp\Cancellation;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\FixedClock;
use Cspray\HttpClientTestInterceptor\Helper\FixtureAndRequestCollectingMatcherStrategy;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixtureRepository;
use Cspray\HttpClientTestInterceptor\HttpMock\MockResponse;
use Cspray\HttpClientTestInterceptor\Interceptor\FixtureAwareInterceptor;
use Cspray\HttpClientTestInterceptor\Interceptor\TestingInterceptorLogger;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Interceptor\FixtureAwareInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class FixtureAwareInterceptorTest extends TestCase {

    public function testFixtureRepositoryEmptySavesResponseFromDelegatedHttpClient() : void {
        $fixtureRepo = new StubFixtureRepository();
        $requestMatchingStrategy = $this->getMockBuilder(MatcherStrategy::class)->getMock();
        $requestMatchingStrategy->expects($this->never())->method('doesFixtureMatchRequest');
        $clock = new FixedClock($date = new DateTimeImmutable('2022-01-01 12:00:00'));

        $subject = new FixtureAwareInterceptor($fixtureRepo, $requestMatchingStrategy, $clock);

        $request = new Request('http://example.com');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );
        $cancellation = $this->getMockBuilder(Cancellation::class)->getMock();
        $httpClient = $this->getMockBuilder(DelegateHttpClient::class)->getMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($request)
            ->willReturn($response);

        $actualResponse = $subject->request($request, $cancellation, $httpClient);

        self::assertSame($response, $actualResponse);

        $fixtures = iterator_to_array($fixtureRepo->getFixtures());

        self::assertCount(1, $fixtures);

        $fixture = $fixtures[0];
        self::assertInstanceOf(Fixture::class, $fixture);
        self::assertNotNull($fixture->getId());
        self::assertSame($fixture->getCreatedAt(), $date);
        self::assertSame($request, $fixture->getRequest());
        self::assertSame($response, $fixture->getResponse());
    }

    public function testFixtureRepositoryHasFixtureNotMatchWillReturnDelegatedHttpClient() : void {
        $fixtureRepo = new StubFixtureRepository(
            $fixture1 = StubFixture::fromRequest(new Request('http://example.com')),
            $fixture2 = StubFixture::fromRequest(new Request('http://www.example.com'))
        );

        $request = new Request('http://sub.example.com');
        $matcherStrategy = new FixtureAndRequestCollectingMatcherStrategy(false, 'Mock strategy log');
        $clock = new FixedClock($date = new DateTimeImmutable('2022-01-01 12:00:00'));

        $subject = new FixtureAwareInterceptor($fixtureRepo, $matcherStrategy, $clock);

        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );
        $cancellation = $this->getMockBuilder(Cancellation::class)->getMock();
        $httpClient = $this->getMockBuilder(DelegateHttpClient::class)->getMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($request)
            ->willReturn($response);

        $actualResponse = $subject->request($request, $cancellation, $httpClient);

        self::assertSame([
            [$fixture1, $request],
            [$fixture2, $request]
        ], $matcherStrategy->getPairs());

        self::assertSame($response, $actualResponse);

        $fixtures = iterator_to_array($fixtureRepo->getFixtures());

        // Expecting 3, the 2 that were already present and the 1 that gets added
        self::assertCount(3, $fixtures);

        $fixture = $fixtures[2];
        self::assertInstanceOf(Fixture::class, $fixture);
        self::assertNotNull($fixture->getId());
        self::assertSame($fixture->getCreatedAt(), $date);
        self::assertSame($request, $fixture->getRequest());
        self::assertSame($response, $fixture->getResponse());
    }

    public function testFixtureRepositoryHasFixtureDoesMatchWillReturnDelegatedHttpClient() : void {
        $fixtureRepo = new StubFixtureRepository(
            $fixture1 = StubFixture::fromRequest(new Request('http://example.com')),
            StubFixture::fromRequest(new Request('http://www.example.com'))
        );

        $request = new Request('http://sub.example.com');
        $requestMatchingStrategy = $this->getMockBuilder(MatcherStrategy::class)->getMock();
        $requestMatchingStrategy->expects($this->exactly(1))
            ->method('doesFixtureMatchRequest')
            ->with($fixture1, $request)
            ->willReturn(new MatcherStrategyResult(
                true,
                $request,
                $fixture1,
                $requestMatchingStrategy,
                [new MatcherDiff('label', 'Mocked request success')]
            ));
        $clock = new FixedClock(new DateTimeImmutable('2022-01-01 12:00:00'));

        $subject = new FixtureAwareInterceptor($fixtureRepo, $requestMatchingStrategy, $clock);

        $cancellation = $this->getMockBuilder(Cancellation::class)->getMock();
        $httpClient = $this->getMockBuilder(DelegateHttpClient::class)->getMock();
        $httpClient->expects($this->never())->method('request');

        $actualResponse = $subject->request($request, $cancellation, $httpClient);

        self::assertSame($fixture1->getResponse(), $actualResponse);
        self::assertSame($actualResponse->getRequest(), $request);

        self::assertSame($fixture1->getId()->toString(), $actualResponse->getHeader('HttpClient-TestInterceptor-Fixture-Id'));

        $fixtures = iterator_to_array($fixtureRepo->getFixtures());

        // Expecting only the 2 that were already present
        self::assertCount(2, $fixtures);
    }

    public function testMatcherResultPassedToLogger() : void {
        $fixtureRepo = new StubFixtureRepository(
            $fixture1 = StubFixture::fromRequest(new Request('http://example.com')),
        );

        $request = new Request('http://example.com');
        $requestMatchingStrategy = $this->getMockBuilder(MatcherStrategy::class)->getMock();
        $requestMatchingStrategy->expects($this->exactly(1))
            ->method('doesFixtureMatchRequest')
            ->with($fixture1, $request)
            ->willReturn(new MatcherStrategyResult(
                true,
                $request,
                $fixture1,
                $requestMatchingStrategy,
                [new MatcherDiff('label', 'Mocked request success')]
            ));
        $clock = new FixedClock(new DateTimeImmutable('2022-01-01 12:00:00'));

        $subject = new FixtureAwareInterceptor($fixtureRepo, $requestMatchingStrategy, $clock);
        $request = new Request('http://example.com');

        $logger = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with($fixture1, $request, $this->isInstanceOf(MatcherStrategyResult::class));

        $subject->addLogger($logger);

        $cancellation = $this->getMockBuilder(Cancellation::class)->getMock();
        $httpClient = $this->getMockBuilder(DelegateHttpClient::class)->getMock();
        $httpClient->expects($this->never())->method('request');

        $subject->request($request, $cancellation, $httpClient);
    }

    public function testGettingAddedObservers() : void {
        $loggerA = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();
        $loggerB = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();

        $fixtureRepo = new StubFixtureRepository();
        $requestMatchingStrategy = $this->getMockBuilder(MatcherStrategy::class)->getMock();
        $requestMatchingStrategy->expects($this->never())->method('doesFixtureMatchRequest');
        $clock = new FixedClock(new DateTimeImmutable('2022-01-01 12:00:00'));

        $subject = new FixtureAwareInterceptor($fixtureRepo, $requestMatchingStrategy, $clock);

        $subject->addLogger($loggerA);
        $subject->addLogger($loggerB);

        self::assertSame([$loggerA, $loggerB], $subject->getLoggers());
    }

    public function testRemovingObservers() : void {
        $loggerA = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();
        $loggerB = $this->getMockBuilder(TestingInterceptorLogger::class)->getMock();

        $fixtureRepo = new StubFixtureRepository();
        $requestMatchingStrategy = $this->getMockBuilder(MatcherStrategy::class)->getMock();
        $requestMatchingStrategy->expects($this->never())->method('doesFixtureMatchRequest');
        $clock = new FixedClock(new DateTimeImmutable('2022-01-01 12:00:00'));

        $subject = new FixtureAwareInterceptor($fixtureRepo, $requestMatchingStrategy, $clock);

        $subject->addLogger($loggerA);
        $subject->addLogger($loggerB);

        self::assertSame([$loggerA, $loggerB], $subject->getLoggers());

        $subject->removeLogger($loggerB);

        self::assertSame([$loggerA], $subject->getLoggers());
    }

}
