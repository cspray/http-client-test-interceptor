<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\BodyMatcherStrategy;
use PHPUnit\Framework\Attributes\CoversClass;

#[
    CoversClass(BodyMatcherStrategy::class),
    CoversClass(Matcher::class),
    CoversClass(MatcherDiff::class),
    CoversClass(MatcherStrategyResult::class),
]
final class BodyMatcherNullRequestBodyTest extends MatcherStrategyTestCase {

    protected function subject() : MatcherStrategy {
        return Matcher::Body->getStrategy();
    }

    protected function request() : Request {
        return new Request('http://example.com');
    }

    protected function matchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com'));
    }

    protected function nonMatchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com', body: 'My non-empty body'));
    }

    protected function expectedDiffLabel() : string {
        return 'body';
    }

    protected function expectedNonMatchingDiff() : string {
        return <<<TEXT
--- Fixture
+++ Request
@@ @@
-My non-empty body

TEXT;

    }
}