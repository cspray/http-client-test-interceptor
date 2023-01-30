<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\MethodMatcher
 */
final class MethodMatcherTest extends TestCase {

    private MethodMatcher $subject;

    protected function setUp() : void {
        $this->subject = new MethodMatcher();
    }

    public function testMethodMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', 'PUT'));
        $request = new Request('http://not.example.com', 'PUT');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testMethodDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://not.example.com', 'PUT');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }


}