<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\UriMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class UriSchemeMatcherTest extends MatcherStrategyTestCase {

    protected function subject() : MatcherStrategy {
        return Matcher::Uri->getStrategy();
    }

    protected function request() : Request {
        return new Request('http://example.com');
    }

    protected function matchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('http://example.com'));
    }

    protected function nonMatchingFixture() : Fixture {
        return StubFixture::fromRequest(new Request('https://example.com'));
    }

    protected function expectedDiffLabel() : string {
        return 'uri';
    }

    protected function expectedNonMatchingDiff() : string {
        return <<<TEXT
--- Fixture
+++ Request
@@ @@
-https://example.com
+http://example.com

TEXT;
    }
}
