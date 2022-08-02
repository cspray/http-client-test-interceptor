<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\UriMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\UriMatcher
 */
final class UriMatcherTest extends TestCase {

    private UriMatcher $subject;

    protected function setUp() : void {
        $this->subject = new UriMatcher();
    }

    public function testCheckSchemeAndHostMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://example.com', 'POST');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckSchemeDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('https://example.com'));
        $request = new Request('http://example.com', 'POST');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckHostDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com'));
        $request = new Request('http://not.example.com');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckWithMatchingSchemeHostAndPortReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com:4200'));
        $request = new Request('http://example.com:4200');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckPortDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com:81'));
        $request = new Request('http://example.com');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckImplicitPortHttpReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com:80'));
        $request = new Request('http://example.com');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckMatchingSchemeHostPathReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com/my/path'));
        $request = new Request('http://example.com/my/path');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckPathDoesNotMatchReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com/my/other/path'));
        $request = new Request('http://example.com/my/path');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckSchemeHostQueryParametersMatchReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com?foo=bar&bar=baz'));
        $request = new Request('http://example.com?foo=bar&bar=baz');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckQueryParametersNotOrderDependentReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com?foo=bar&bar=baz'));
        $request = new Request('http://example.com?bar=baz&foo=bar');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckingQueryParametersDifferentReturnsFalse() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com?foo=bar&bar=bay'));
        $request = new Request('http://example.com?bar=baz&foo=bar');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckSchemeHostFragmentMatchesReturnsTrue() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com#fragment'));
        $request = new Request('http://example.com#fragment');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testCheckFragmentDoesNotMatch() : void {
        $fixture = StubFixture::fromRequest(new Request('http://example.com#fragment'));
        $request = new Request('http://example.com#frag');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }
}