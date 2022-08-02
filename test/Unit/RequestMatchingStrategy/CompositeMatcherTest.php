<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\RequestMatchingStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher
 */
final class CompositeMatcherTest extends TestCase {

    public function testCompositeMatchersOnePassed() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://example.com');

        $matcher = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher->expects($this->once())
            ->method('doesFixtureMatchRequest')
            ->with($fixture, $request)
            ->willReturn(true);

        $subject = new CompositeMatcher($matcher);

        self::assertTrue($subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCompositeMatchersMultiplePassed() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://example.com');

        $matcher = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher->expects($this->once())
            ->method('doesFixtureMatchRequest')
            ->with($fixture, $request)
            ->willReturn(true);

        $matcher2 = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher2->expects($this->once())
            ->method('doesFixtureMatchRequest')
            ->with($fixture, $request)
            ->willReturn(true);

        $matcher3 = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher3->expects($this->once())
            ->method('doesFixtureMatchRequest')
            ->with($fixture, $request)
            ->willReturn(true);

        $subject = new CompositeMatcher($matcher, $matcher2, $matcher3);

        self::assertTrue($subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCompositeMatchersPassedSomeReturnFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://example.com');

        $matcher = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher->expects($this->once())
            ->method('doesFixtureMatchRequest')
            ->with($fixture, $request)
            ->willReturn(true);

        $matcher2 = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher2->expects($this->once())
            ->method('doesFixtureMatchRequest')
            ->with($fixture, $request)
            ->willReturn(false);

        $matcher3 = $this->getMockBuilder(RequestMatchingStrategy::class)->getMock();
        $matcher3->expects($this->never())
            ->method('doesFixtureMatchRequest');

        $subject = new CompositeMatcher($matcher, $matcher2, $matcher3);

        self::assertFalse($subject->doesFixtureMatchRequest($fixture, $request));
    }
}