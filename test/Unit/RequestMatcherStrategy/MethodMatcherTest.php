<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\MethodMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class MethodMatcherTest extends MatcherStrategyTestCase {

    protected function subject() : MatcherStrategy {
        return Matcher::Method->getStrategy();
    }

    protected function request() : Request {
        return new Request('http://example.com', 'PUT');
    }

    protected function matchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com', 'PUT'));
    }

    protected function nonMatchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com', 'GET'));
    }

    protected function expectedDiffLabel() : string {
        return 'method';
    }

    protected function expectedNonMatchingDiff() : string {
        return <<<TEXT
--- Fixture
+++ Request
@@ @@
-GET
+PUT

TEXT;
    }

}
