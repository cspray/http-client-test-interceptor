<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\HeadersMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\HeadersMatcher
 */
final class HeadersMatcherTest extends TestCase {

    private HeadersMatcher $subject;

    protected function setUp() : void {
        $this->subject = new HeadersMatcher();
    }

    public function testEmptyHeadersMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://www.example.com');
            // make sure this is explicitly set for the test
            $request->setHeaders([]);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setHeaders([]);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testSingleHeaderDoNotMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders(['Accept' => 'application/json']);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', 'text/plain');

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testSingleHeaderDoesMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders(['Accept' => 'text/plain']);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', 'text/plain');

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testSingleHeaderMultipleValuesNotOrderDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders(['Accept' => ['text/plain', 'text/html']]);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', ['text/html', 'text/plain']);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testMultipleHeaderMultipleValuesNotOrderDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders([
                'Accept' => ['text/plain', 'text/html'],
                'Custom' => ['foo', 'bar']
            ]);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', ['text/html', 'text/plain']);
        $request->setHeader('Custom', ['bar', 'foo']);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testMultipleHeaderKeysNotOrderDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://example.com');
            $request->setHeaders([
                'Accept' => ['text/plain', 'text/html'],
                'Custom' => ['foo', 'bar']
            ]);
            return $request;
        });
        $request = new Request('http://exampmle.net');
        $request->setHeader('Custom', ['bar', 'foo']);
        $request->setHeader('Accept', ['text/html', 'text/plain']);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }
}