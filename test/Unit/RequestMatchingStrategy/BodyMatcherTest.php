<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Amp\Http\Client\Body\StringBody;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\BodyMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\BodyMatcher
 */
final class BodyMatcherTest extends TestCase {

    private BodyMatcher $subject;

    protected function setUp() : void {
        $this->subject = new BodyMatcher();
    }

    public function testBodyMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', body: 'The request body'));
        $request = new Request('http://not.example.com', body: new StringBody('The request body'));

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testBodyDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com', body: 'A different request body'));
        $request = new Request('http://not.example.com', body: new StringBody('The request body'));

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

}