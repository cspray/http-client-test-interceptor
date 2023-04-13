<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\BufferedContent;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class BodyMatcherTest extends MatcherStrategyTestCase {

    protected function subject() : MatcherStrategy {
        return Matcher::Body->getStrategy();
    }

    protected function request() : Request {
        return new Request('http://not.example.com', body: BufferedContent::fromString('The request body'));
    }

    protected function matchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com', body: 'The request body'));
    }

    protected function nonMatchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com', body: 'A different request body'));
    }

    protected function expectedDiffLabel() : string {
        return 'body';
    }

    protected function expectedNonMatchingDiff() : string {
        return <<<TEXT
--- Fixture
+++ Request
@@ @@
-A different request body
+The request body

TEXT;
    }
}
