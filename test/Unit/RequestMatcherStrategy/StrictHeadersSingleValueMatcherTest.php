<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\RequestMatcherStrategy;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\StrictHeadersMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\MatcherDiff
 */
final class StrictHeadersSingleValueMatcherTest extends MatcherStrategyTestCase {

    protected function subject() : MatcherStrategy {
        return Matcher::Headers->getStrategy();
    }

    protected function request() : Request {
        $request = new Request('http://exampmle.net');
        $request->setHeader('Accept', 'text/plain');
        return $request;
    }

    protected function matchingFixture() : Fixture {
        return StubFixture::fromRequestFactory(function() {
            $request = new Request('http://www.example.com');
            $request->setHeader('Accept', 'text/plain');
            return $request;
        });
    }

    protected function nonMatchingFixture() : Fixture {
        return StubFixture::fromRequestFactory(function() {
            $request = new Request('http://www.example.com');
            $request->setHeader('Accept', 'application/json');
            return $request;
        });
    }

    protected function expectedDiffLabel() : string {
        return 'headers';
    }

    protected function expectedNonMatchingDiff() : string {
        return <<<TEXT
--- Fixture
+++ Request
@@ @@
-Accept: application/json
+Accept: text/plain

TEXT;
    }
}
