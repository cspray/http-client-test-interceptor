<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\ProtocolVersionMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class ProtocolVersionsMatcherTest extends MatcherStrategyTestCase {

    protected function subject() : MatcherStrategy {
        return Matcher::ProtocolVersions->getStrategy();
    }

    protected function request() : Request {
        $request = new Request('http://example.com');
        $request->setProtocolVersions(['1.0', '1.1', '2']);
        return $request;
    }

    protected function matchingFixture() : Fixture {
        return StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['2', '1.1', '1.0']);
            return $request;
        });
    }

    protected function nonMatchingFixture() : Fixture {
        return StubFixture::fromRequestFactory(function() {
            $request = new Request('http://sub.example.com');
            $request->setProtocolVersions(['2', '1.0']);
            return $request;
        });
    }

    protected function expectedDiffLabel() : string {
        return 'protocol';
    }

    protected function expectedNonMatchingDiff() : string {
        return <<<TEXT
--- Fixture
+++ Request
@@ @@
-1.0, 2
+1.0, 1.1, 2

TEXT;
    }
}
