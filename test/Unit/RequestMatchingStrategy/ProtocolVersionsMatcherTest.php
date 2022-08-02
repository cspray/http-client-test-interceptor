<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatchingStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\ProtocolVersionsMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\ProtocolVersionsMatcher
 */
final class ProtocolVersionsMatcherTest extends TestCase {

    private ProtocolVersionsMatcher $subject;

    protected function setUp() : void {
        $this->subject = new ProtocolVersionsMatcher();
    }

    public function testSingleProtocolVersionMatches() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.0']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0']);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testSingleProtocolVersionDoNotMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.0']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.1']);

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testMultipleProtocolVersionsMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.0', '2']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0', '2']);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testMultipleProtocolVersionsDoNotMatch() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.1', '2']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0', '2']);

        self::assertFalse($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

    public function testMultipleProtocolVersionsAreNotSortDependent() : void {
        $fixture = StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['1.1', '2']);
            return $request;
        });
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['2', '1.1']);

        self::assertTrue($this->subject->doesFixtureMatchRequest($fixture, $request));
    }

}